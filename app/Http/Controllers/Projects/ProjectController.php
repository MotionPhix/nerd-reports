<?php

namespace App\Http\Controllers\Projects;

use App\Http\Controllers\Controller;
use App\Services\ProjectManagementService;
use App\Models\Project;
use App\Models\Contact;
use App\Models\Firm;
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

    $project->load([
      'contact.firm',
      'author',
      'boards.tasks.user',
      'tasks.user',
      'interactions.user'
    ]);

    $stats = $this->projectService->getProjectStats($project);
    $progress = $this->projectService->getProjectProgress($project);
    $timeline = $this->projectService->getProjectTimeline($project);

    return Inertia::render('projects/Show', [
      'project' => $project,
      'stats' => $stats,
      'progress' => $progress,
      'timeline' => $timeline,
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
      'defaultContact' => $request->contact_id,
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
      'status' => 'nullable|in:in_progress,approved,completed,cancelled,done',
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

    $project->load(['contact.firm']);

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
      'status' => 'required|in:in_progress,approved,completed,cancelled,done',
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
