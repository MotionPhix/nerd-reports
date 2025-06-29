<?php

namespace App\Services;

use App\Enums\ProjectStatus;
use App\Models\Board;
use App\Models\Contact;
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

      // Create default boards for the project
      $this->createDefaultBoards($project);

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
   * Get projects for a user with filters
   */
  public function getUserProjects(User $user, array $filters = []): Collection
  {
    $query = Project::where('created_by', $user->id)
      ->orWhereHas('tasks', function ($q) use ($user) {
        $q->where('assigned_to', $user->id);
      })
      ->with(['contact.firm', 'tasks', 'boards']);

    // Apply filters
    if (isset($filters['status'])) {
      $query->where('status', $filters['status']);
    }

    if (isset($filters['contact_id'])) {
      $query->where('contact_id', $filters['contact_id']);
    }

    if (isset($filters['firm_id'])) {
      $query->whereHas('contact', function ($q) use ($filters) {
        $q->where('firm_id', $filters['firm_id']);
      });
    }

    if (isset($filters['due_date_from'])) {
      $query->where('due_date', '>=', Carbon::parse($filters['due_date_from']));
    }

    if (isset($filters['due_date_to'])) {
      $query->where('due_date', '<=', Carbon::parse($filters['due_date_to']));
    }

    if (isset($filters['overdue']) && $filters['overdue']) {
      $query->where('due_date', '<', now())
        ->whereNotIn('status', [ProjectStatus::COMPLETED, ProjectStatus::CANCELLED]);
    }

    // Search
    if (isset($filters['search'])) {
      $search = $filters['search'];
      $query->where(function ($q) use ($search) {
        $q->where('name', 'like', "%{$search}%")
          ->orWhere('description', 'like', "%{$search}%")
          ->orWhereHas('contact', function ($contactQuery) use ($search) {
            $contactQuery->where('first_name', 'like', "%{$search}%")
              ->orWhere('last_name', 'like', "%{$search}%");
          });
      });
    }

    // Ordering
    $orderBy = $filters['order_by'] ?? 'created_at';
    $orderDirection = $filters['order_direction'] ?? 'desc';
    $query->orderBy($orderBy, $orderDirection);

    return $query->get();
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
      'boards_count' => $project->boards()->count(),
    ];
  }

  /**
   * Get overdue projects
   */
  public function getOverdueProjects(User $user = null): Collection
  {
    $query = Project::where('due_date', '<', now())
      ->whereNotIn('status', [ProjectStatus::COMPLETED, ProjectStatus::CANCELLED])
      ->with(['contact.firm', 'tasks']);

    if ($user) {
      $query->where(function ($q) use ($user) {
        $q->where('created_by', $user->id)
          ->orWhereHas('tasks', function ($taskQuery) use ($user) {
            $taskQuery->where('assigned_to', $user->id);
          });
      });
    }

    return $query->orderBy('due_date', 'asc')->get();
  }

  /**
   * Get projects with recent activity
   */
  public function getRecentlyActiveProjects(User $user, int $days = 7): Collection
  {
    $since = now()->subDays($days);

    return Project::where(function ($query) use ($user) {
      $query->where('created_by', $user->id)
        ->orWhereHas('tasks', function ($q) use ($user) {
          $q->where('assigned_to', $user->id);
        });
    })
      ->where(function ($query) use ($since) {
        $query->where('updated_at', '>=', $since)
          ->orWhereHas('tasks', function ($q) use ($since) {
            $q->where('updated_at', '>=', $since);
          });
      })
      ->with(['contact.firm', 'tasks' => function ($q) use ($since) {
        $q->where('updated_at', '>=', $since);
      }])
      ->orderBy('updated_at', 'desc')
      ->get();
  }

  /**
   * Archive a project
   */
  public function archiveProject(Project $project): Project
  {
    $project->update([
      'status' => ProjectStatus::COMPLETED,
    ]);

    Log::info("Project archived", [
      'project_id' => $project->uuid,
      'name' => $project->name,
    ]);

    return $project;
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
   * Create default boards for a new project
   */
  private function createDefaultBoards(Project $project): void
  {
    $defaultBoards = [
      ['name' => 'To Do', 'slug' => 'todo'],
      ['name' => 'In Progress', 'slug' => 'in-progress'],
      ['name' => 'Review', 'slug' => 'review'],
      ['name' => 'Done', 'slug' => 'done'],
    ];

    foreach ($defaultBoards as $boardData) {
      Board::create([
        'name' => $boardData['name'],
        'slug' => $boardData['slug'],
        'project_id' => $project->uuid,
      ]);
    }
  }

  /**
   * Handle project status changes
   */
  private function handleStatusChange(Project $project, ProjectStatus $oldStatus, ProjectStatus $newStatus): void
  {
    // Log status change
    Log::info("Project status changed", [
      'project_id' => $project->uuid,
      'from_status' => $oldStatus->value,
      'to_status' => $newStatus->value,
    ]);

    // Additional logic for status changes can be added here
    // For example, notifications, webhooks, etc.
  }

  /**
   * Calculate average task completion time
   */
  private function calculateAverageCompletionTime(Collection $completedTasks): ?float
  {
    $tasksWithTimes = $completedTasks->filter(function ($task) {
      return $task->started_at && $task->completed_at;
    });

    if ($tasksWithTimes->isEmpty()) {
      return null;
    }

    $totalHours = $tasksWithTimes->sum(function ($task) {
      return $task->started_at->diffInHours($task->completed_at);
    });

    return round($totalHours / $tasksWithTimes->count(), 1);
  }

  /**
   * Get project timeline data
   */
  public function getProjectTimeline(Project $project): array
  {
    $tasks = $project->tasks()
      ->whereNotNull('started_at')
      ->orderBy('started_at')
      ->get();

    $timeline = [];

    foreach ($tasks as $task) {
      $timeline[] = [
        'id' => $task->uuid,
        'name' => $task->name,
        'start_date' => $task->started_at,
        'end_date' => $task->completed_at ?? $task->due_date,
        'status' => $task->status->value,
        'assigned_to' => $task->user->name ?? 'Unassigned',
        'progress' => $task->status->value === 'completed' ? 100 :
          ($task->status->value === 'in_progress' ? 50 : 0),
      ];
    }

    return $timeline;
  }
}
