<?php

namespace App\Services;

use App\Enums\ProjectStatus;
use App\Models\Project;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProjectManagementService
{
  /**
   * Create a new project
   */
  public function createProject(array $data): Project
  {
    return DB::transaction(function () use ($data) {
      $project = Project::create([
        'name' => $data['name'],
        'description' => $data['description'] ?? null,
        'contact_id' => $data['contact_id'],
        'created_by' => $data['created_by'] ?? auth()->id(),
        'status' => $data['status'] ?? ProjectStatus::PENDING,
        'due_date' => isset($data['due_date']) ? Carbon::parse($data['due_date']) : null,
      ]);

      Log::info("Project created", [
        'project_id' => $project->uuid,
        'name' => $project->name,
        'contact_id' => $project->contact_id,
        'created_by' => $project->created_by,
      ]);

      return $project;
    });
  }

  /**
   * Update an existing project
   */
  public function updateProject(Project $project, array $data): Project
  {
    $originalStatus = $project->status;

    $project->update([
      'name' => $data['name'] ?? $project->name,
      'description' => $data['description'] ?? $project->description,
      'status' => $data['status'] ?? $project->status,
      'due_date' => isset($data['due_date']) ? Carbon::parse($data['due_date']) : $project->due_date,
      'deadline' => $data['deadline'] ?? null,
      'priority' => $data['priority'] ?? 'medium',
      'estimated_hours' => $data['estimated_hours'] ?? null,
      'budget' => $data['budget'] ?? null,
      'hourly_rate' => $data['hourly_rate'] ?? null,
      'is_billable' => $data['is_billable'] ?? false,
      'notes' => $data['notes'] ?? null,
    ]);

    // Handle status changes
    if ($originalStatus !== $project->status) {
      $this->handleStatusChange($project, $originalStatus, $project->status);
    }

    Log::info("Project updated", [
      'project_id' => $project->uuid,
      'changes' => $project->getChanges(),
    ]);

    return $project;
  }

  /**
   * Get projects for a user with filters - using model scopes
   */
  public function getUserProjects(User $user, array $filters = []): Collection
  {
    $query = Project::where('created_by', $user->id)
      ->orWhereHas('tasks', function ($q) use ($user) {
        $q->where('assigned_to', $user->id);
      })
      ->with(['contact.firm', 'tasks']);

    // Apply filters using model scopes
    $query->byStatus($filters['status'] ?? null)
      ->byContact($filters['contact_id'] ?? null)
      ->byFirm($filters['firm_id'] ?? null)
      ->byDateRange($filters['due_date_from'] ?? null, $filters['due_date_to'] ?? null)
      ->search($filters['search'] ?? null);

    // Handle overdue filter
    if (isset($filters['overdue']) && $filters['overdue']) {
      $query->overdue();
    }

    // Apply ordering
    $orderBy = $filters['order_by'] ?? 'created_at';
    $orderDirection = $filters['order_direction'] ?? 'desc';
    $query->orderBy($orderBy, $orderDirection);

    return $query->get();
  }

  /**
   * Get overdue projects for a user
   */
  public function getOverdueProjects(User $user): Collection
  {
    return Project::where('created_by', $user->id)
      ->orWhereHas('tasks', function ($q) use ($user) {
        $q->where('assigned_to', $user->id);
      })
      ->overdue()
      ->with(['contact.firm', 'tasks'])
      ->orderBy('due_date', 'asc')
      ->get();
  }

  /**
   * Get recently active projects for a user
   */
  public function getRecentlyActiveProjects(User $user, int $days = 7): Collection
  {
    $startDate = now()->subDays($days);

    return Project::where('created_by', $user->id)
      ->orWhereHas('tasks', function ($q) use ($user) {
        $q->where('assigned_to', $user->id);
      })
      ->where(function ($query) use ($startDate) {
        $query->where('updated_at', '>=', $startDate)
          ->orWhereHas('tasks', function ($taskQuery) use ($startDate) {
            $taskQuery->where('updated_at', '>=', $startDate);
          })
          ->orWhereHas('interactions', function ($interactionQuery) use ($startDate) {
            $interactionQuery->where('created_at', '>=', $startDate);
          });
      })
      ->with(['contact.firm', 'tasks'])
      ->orderBy('updated_at', 'desc')
      ->get();
  }

  /**
   * Get project statistics
   */
  public function getProjectStats(Project $project, Carbon $startDate = null, Carbon $endDate = null): array
  {
    $startDate = $startDate ?? $project->created_at;
    $endDate = $endDate ?? now();

    $tasks = $project->tasks()
      ->whereBetween('created_at', [$startDate, $endDate])
      ->get();

    $completedTasks = $tasks->where('status.value', 'completed');
    $totalHours = $tasks->sum('actual_hours');
    $estimatedHours = $tasks->sum('estimated_hours');

    return [
      'total_tasks' => $tasks->count(),
      'completed_tasks' => $completedTasks->count(),
      'in_progress_tasks' => $tasks->where('status.value', 'in_progress')->count(),
      'todo_tasks' => $tasks->where('status.value', 'todo')->count(),
      'overdue_tasks' => $tasks->where('due_date', '<', now())
        ->whereNotIn('status.value', ['completed', 'cancelled'])
        ->count(),
      'total_hours' => $totalHours,
      'estimated_hours' => $estimatedHours,
      'hours_variance' => $estimatedHours > 0 ? round((($totalHours - $estimatedHours) / $estimatedHours) * 100, 1) : 0,
      'completion_rate' => $tasks->count() > 0 ? round(($completedTasks->count() / $tasks->count()) * 100, 1) : 0,
      'average_task_completion_time' => $this->calculateAverageCompletionTime($completedTasks),
      'team_members' => $tasks->pluck('assigned_to')->unique()->count(),
    ];
  }

  /**
   * Get project progress
   */
  public function getProjectProgress(Project $project): array
  {
    $tasks = $project->tasks;
    $totalTasks = $tasks->count();

    if ($totalTasks === 0) {
      return [
        'percentage' => 0,
        'completed_tasks' => 0,
        'total_tasks' => 0,
        'status' => 'No tasks',
      ];
    }

    $completedTasks = $tasks->where('status.value', 'completed')->count();
    $percentage = round(($completedTasks / $totalTasks) * 100, 1);

    $status = match (true) {
      $percentage === 100 => 'Completed',
      $percentage >= 75 => 'Nearly Complete',
      $percentage >= 50 => 'In Progress',
      $percentage >= 25 => 'Getting Started',
      default => 'Just Started',
    };

    return [
      'percentage' => $percentage,
      'completed_tasks' => $completedTasks,
      'total_tasks' => $totalTasks,
      'status' => $status,
    ];
  }

  /**
   * Add progress and stats to a collection of projects
   */
  public function addProgressAndStats(Collection $projects): Collection
  {
    return $projects->map(function ($project) {
      $project->progress = $this->getProjectProgress($project);
      $project->stats = $this->getProjectStats($project);
      return $project;
    });
  }

  /**
   * Handle status change logic
   */
  private function handleStatusChange(Project $project, ProjectStatus $oldStatus, ProjectStatus $newStatus): void
  {
    Log::info("Project status changed", [
      'project_id' => $project->uuid,
      'old_status' => $oldStatus->value,
      'new_status' => $newStatus->value,
    ]);

    // Add any status change logic here (notifications, etc.)
  }

  /**
   * Calculate average task completion time
   */
  private function calculateAverageCompletionTime(Collection $completedTasks): float
  {
    if ($completedTasks->isEmpty()) {
      return 0;
    }

    $totalDays = $completedTasks->sum(function ($task) {
      if (!$task->completed_at || !$task->created_at) {
        return 0;
      }
      return $task->created_at->diffInDays($task->completed_at);
    });

    return round($totalDays / $completedTasks->count(), 1);
  }
}
