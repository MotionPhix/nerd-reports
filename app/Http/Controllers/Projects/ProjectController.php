<?php

namespace App\Http\Controllers\Projects;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\Firm;
use App\Models\Project;
use App\Services\ProjectManagementService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProjectController extends Controller
{
  public function __construct(
    private ProjectManagementService $projectService
  ) {}

  public function index(Request $request)
  {
    $user = auth()->user();

    // Get filters from request
    $filters = $request->only([
      'status',
      'contact_id',
      'firm_id',
      'due_date_from',
      'due_date_to',
      'overdue',
      'search',
      'order_by',
      'order_direction'
    ]);

    // Get projects with filters applied
    $projects = $this->projectService->getUserProjects($user, $filters);

    // Get overdue projects
    $overdueProjects = $this->projectService->getOverdueProjects($user);

    // Get recently active projects
    $recentlyActive = $this->projectService->getRecentlyActiveProjects($user);

    // Add progress and stats to all project collections
    $projects = $this->projectService->addProgressAndStats($projects);
    $overdueProjects = $this->projectService->addProgressAndStats($overdueProjects);
    $recentlyActive = $this->projectService->addProgressAndStats($recentlyActive);

    // Get contacts and firms for filters
    $contacts = Contact::select(['uuid', 'first_name', 'last_name'])
      ->selectRaw("CONCAT(first_name, ' ', last_name) as full_name")
      ->with('firm:uuid,name')
      ->orderBy('first_name')
      ->get();

    $firms = Firm::select(['uuid', 'name'])
      ->orderBy('name')
      ->get();

    return Inertia::render('projects/Index', [
      'projects' => $projects,
      'overdueProjects' => $overdueProjects,
      'recentlyActive' => $recentlyActive,
      'filters' => $filters,
      'contacts' => $contacts,
      'firms' => $firms,
    ]);
  }

  public function show(Project $project)
  {
    $project->load(['contact.firm', 'tasks.assignedUser', 'author']);

    // Add progress and stats
    $project->progress = $this->projectService->getProjectProgress($project);
    $project->stats = $this->projectService->getProjectStats($project);

    return Inertia::render('projects/Show', [
      'project' => $project,
    ]);
  }

  public function create()
  {
    $contacts = Contact::with('firm')->get();

    return Inertia::render('projects/Create', [
      'contacts' => $contacts,
    ]);
  }

  public function store(Request $request)
  {
    $validated = $request->validate([
      'name' => 'required|string|max:255',
      'description' => 'nullable|string',
      'contact_id' => 'required|exists:contacts,uuid',
      'due_date' => 'nullable|date',
      'status' => 'nullable|string',
      'priority' => 'nullable|string',
      'estimated_hours' => 'nullable|numeric',
      'budget' => 'nullable|numeric',
      'hourly_rate' => 'nullable|numeric',
      'is_billable' => 'boolean',
      'notes' => 'nullable|string',
    ]);

    $project = $this->projectService->createProject($validated);

    return redirect()->route('projects.show', $project)
      ->with('success', 'Project created successfully!');
  }

  public function edit(Project $project)
  {
    $project->load(['contact.firm']);
    $contacts = Contact::with('firm')->get();

    return Inertia::render('projects/Edit', [
      'project' => $project,
      'contacts' => $contacts,
    ]);
  }

  public function update(Request $request, Project $project)
  {
    $validated = $request->validate([
      'name' => 'required|string|max:255',
      'description' => 'nullable|string',
      'contact_id' => 'required|exists:contacts,uuid',
      'due_date' => 'nullable|date',
      'status' => 'nullable|string',
      'priority' => 'nullable|string',
      'estimated_hours' => 'nullable|numeric',
      'budget' => 'nullable|numeric',
      'hourly_rate' => 'nullable|numeric',
      'is_billable' => 'boolean',
      'notes' => 'nullable|string',
    ]);

    $project = $this->projectService->updateProject($project, $validated);

    return redirect()->route('projects.show', $project)
      ->with('success', 'Project updated successfully!');
  }

  public function destroy(Project $project)
  {
    $project->delete();

    return redirect()->route('projects.index')
      ->with('success', 'Project deleted successfully!');
  }

  public function stats(Project $project)
  {
    $project->load(['contact.firm', 'tasks']);

    $stats = $this->projectService->getProjectStats($project);
    $progress = $this->projectService->getProjectProgress($project);

    return Inertia::render('projects/Stats', [
      'project' => $project,
      'stats' => $stats,
      'progress' => $progress,
    ]);
  }
}
