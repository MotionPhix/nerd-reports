<?php

namespace App\Http\Controllers\Projects;

use App\Http\Controllers\Controller;
use App\Services\ProjectManagementService;
use App\Models\Project;
use App\Models\Contact;
use App\Models\Firm;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ProjectController extends Controller
{
  use AuthorizesRequests;

  public function __construct(
    protected ProjectManagementService $projectService
  ) {}

  /**
   * Display projects index
   */
  public function index(Request $request)
  {
    $user = Auth::user();

    $filters = $request->only([
      'status', 'contact_id', 'firm_id', 'due_date_from',
      'due_date_to', 'overdue', 'search', 'order_by', 'order_direction'
    ]);

    $projects = $this->projectService->getUserProjects($user, $filters);
    $overdueProjects = $this->projectService->getOverdueProjects($user);
    $recentlyActive = $this->projectService->getRecentlyActiveProjects($user);

    // Add progress data to projects
    $projects = $projects->map(function ($project) {
      return array_merge($project->toArray(), [
        'progress' => $this->projectService->getProjectProgress($project),
        'stats' => $this->projectService->getProjectStats($project),
      ]);
    });

    return Inertia::render('projects/Index', [
      'projects' => $projects,
      'overdueProjects' => $overdueProjects,
      'recentlyActive' => $recentlyActive,
      'filters' => $filters,
      'contacts' => Contact::with('firm')->get(),
      'firms' => Firm::all(),
    ]);
  }

  /**
   * Show project details
   */
  public function show(Project $project)
  {
    $this->authorize('view', $project);

    // Load basic relationships (removed boards)
    $project->load([
      'contact.firm',
      'author',
      'tasks.user',
      'interactions.user'
    ]);

    // Get project statistics and progress
    $stats = $this->projectService->getProjectStats($project);
    $progress = $this->projectService->getProjectProgress($project);

    // Get tasks with proper relationships
    $tasks = $project->tasks()->with(['user'])->get()->map(function ($task) {
      return [
        'uuid' => $task->uuid,
        'title' => $task->title ?? $task->name,
        'description' => $task->description,
        'status' => $task->status,
        'priority' => $task->priority ?? 'medium',
        'due_date' => $task->due_date,
        'assigned_to' => $task->assigned_to,
        'estimated_hours' => $task->estimated_hours,
        'actual_hours' => $task->actual_hours,
        'created_at' => $task->created_at,
        'updated_at' => $task->updated_at,
        'assignee' => $task->user ? [
          'uuid' => $task->user->uuid ?? $task->user->id,
          'name' => $task->user->first_name . ' ' . $task->user->last_name,
          'avatar_url' => $task->user->avatar_url ?? null,
        ] : null,
      ];
    });

    // Get team members (users assigned to tasks)
    $teamMembers = $project->tasks()
      ->with('user')
      ->whereNotNull('assigned_to')
      ->get()
      ->pluck('user')
      ->unique('id')
      ->filter()
      ->map(function ($user) use ($project) {
        $userTasks = $project->tasks()->where('assigned_to', $user->id)->get();
        return [
          'uuid' => $user->uuid ?? $user->id,
          'name' => $user->first_name . ' ' . $user->last_name,
          'email' => $user->email,
          'role' => 'Team Member',
          'avatar_url' => $user->avatar_url ?? null,
          'hours_logged' => $userTasks->sum('actual_hours') ?? 0,
          'tasks_assigned' => $userTasks->count(),
          'tasks_completed' => $userTasks->where('status', 'completed')->count(),
        ];
      })
      ->values();

    // Get recent time entries (placeholder - implement based on your time tracking system)
    $recentTimeEntries = collect();

    // Get recent activity from interactions and task updates
    $recentActivity = collect();

    // Add project creation activity
    $recentActivity->push([
      'id' => 'project_created',
      'type' => 'project_created',
      'description' => 'Project was created',
      'user' => [
        'name' => $project->author->first_name . ' ' . $project->author->last_name,
        'avatar_url' => $project->author->avatar_url ?? null,
      ],
      'created_at' => Carbon::parse($project->created_at)->toISOString(),
    ]);

    // Add recent task activities
    $project->tasks()->latest()->take(5)->get()->each(function ($task) use (&$recentActivity) {
      $recentActivity->push([
        'id' => 'task_' . $task->id,
        'type' => 'task_created',
        'description' => "Task '{$task->title}' was created",
        'user' => [
          'name' => $task->user ? $task->user->first_name . ' ' . $task->user->last_name : 'System',
          'avatar_url' => $task->user->avatar_url ?? null,
        ],
        'created_at' => Carbon::parse($task->created_at)->toISOString(),
      ]);
    });

    // Sort by date and take latest 10
    $recentActivity = $recentActivity->sortByDesc('created_at')->take(10)->values();

    // Merge stats and progress into project data
    $projectData = array_merge($project->toArray(), [
      'progress' => $progress,
      'stats' => array_merge($stats, [
        'budget_used' => 0, // Implement based on your billing system
        'budget_remaining' => $project->budget ?? 0,
      ]),
    ]);

    return Inertia::render('projects/Show', [
      'project' => $projectData,
      'tasks' => $tasks,
      'teamMembers' => $teamMembers,
      'recentTimeEntries' => $recentTimeEntries,
      'recentActivity' => $recentActivity,
    ]);
  }

  /**
   * Show create project form
   */
  public function create(Request $request)
  {
    return Inertia::render('projects/Create', [
      'contacts' => Contact::with('firm')->get(),
      'firms' => Firm::all(),
      'preselectedContact' => $request->contact_id,
      'preselectedFirm' => $request->firm_id,
    ]);
  }

  /**
   * Store a new project
   */
  public function store(Request $request)
  {
    $request->validate([
      'name' => 'required|string|max:255',
      'description' => 'nullable|string',
      'contact_id' => 'required|exists:contacts,uuid',
      'due_date' => 'nullable|date|after:today',
      'deadline' => 'nullable|date|after:today',
      'status' => 'nullable|in:in_progress,approved,completed,cancelled,done',
      'priority' => 'nullable|in:low,medium,high',
      'estimated_hours' => 'nullable|numeric|min:0',
      'budget' => 'nullable|numeric|min:0',
      'hourly_rate' => 'nullable|numeric|min:0',
      'is_billable' => 'boolean',
      'notes' => 'nullable|string',
      'tags' => 'nullable|array',
      'tags.*' => 'string|max:50',
      'send_notification' => 'boolean',
    ]);

    try {
      $project = $this->projectService->createProject($request->all());

      return redirect()->route('projects.show', $project)
        ->with('success', 'Project created successfully!');
    } catch (\Exception $e) {
      return back()->withErrors(['error' => 'Failed to create project: ' . $e->getMessage()]);
    }
  }

  /**
   * Show edit project form
   */
  public function edit(Project $project)
  {
    $this->authorize('update', $project);

    $project->load(['contact.firm', 'tags']);

    return Inertia::render('projects/Edit', [
      'project' => $project,
      'contacts' => Contact::with('firm')->get(),
      'firms' => Firm::all(),
    ]);
  }

  /**
   * Update a project
   */
  public function update(Request $request, Project $project)
  {
    $this->authorize('update', $project);

    $request->validate([
      'name' => 'required|string|max:255',
      'description' => 'nullable|string',
      'due_date' => 'nullable|date',
      'deadline' => 'nullable|date',
      'status' => 'required|in:in_progress,approved,completed,cancelled,done',
      'priority' => 'nullable|in:low,medium,high',
      'estimated_hours' => 'nullable|numeric|min:0',
      'budget' => 'nullable|numeric|min:0',
      'hourly_rate' => 'nullable|numeric|min:0',
      'is_billable' => 'boolean',
      'notes' => 'nullable|string',
      'tags' => 'nullable|array',
      'tags.*' => 'string|max:50',
    ]);

    try {
      $project = $this->projectService->updateProject($project, $request->all());

      return redirect()->route('projects.show', $project)
        ->with('success', 'Project updated successfully!');
    } catch (\Exception $e) {
      return back()->withErrors(['error' => 'Failed to update project: ' . $e->getMessage()]);
    }
  }

  /**
   * Update project status
   */
  public function updateStatus(Request $request, Project $project)
  {
    $this->authorize('update', $project);

    $request->validate([
      'status' => 'required|in:in_progress,approved,completed,cancelled,done',
    ]);

    try {
      $project->update(['status' => $request->status]);

      return back()->with('success', 'Project status updated successfully!');
    } catch (\Exception $e) {
      return back()->withErrors(['error' => 'Failed to update project status: ' . $e->getMessage()]);
    }
  }

  /**
   * Archive a project
   */
  public function archive(Project $project)
  {
    $this->authorize('update', $project);

    try {
      $this->projectService->archiveProject($project);

      return redirect()->route('projects.index')
        ->with('success', 'Project archived successfully!');
    } catch (\Exception $e) {
      return back()->withErrors(['error' => 'Failed to archive project: ' . $e->getMessage()]);
    }
  }

  /**
   * Delete a project
   */
  public function destroy(Project $project)
  {
    $this->authorize('delete', $project);

    $project->delete();

    return redirect()->route('projects.index')
      ->with('success', 'Project deleted successfully!');
  }

  /**
   * Get project statistics
   */
  public function stats(Project $project)
  {
    $this->authorize('view', $project);

    $stats = $this->projectService->getProjectStats($project);
    $progress = $this->projectService->getProjectProgress($project);

    return response()->json([
      'stats' => $stats,
      'progress' => $progress,
    ]);
  }

  /**
   * Get project timeline
   */
  public function timeline(Project $project)
  {
    $this->authorize('view', $project);

    $timeline = $this->projectService->getProjectTimeline($project);

    return response()->json(['timeline' => $timeline]);
  }
}
