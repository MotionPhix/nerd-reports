<?php

namespace App\Services;

use App\Enums\TaskStatus;
use App\Enums\TaskPriority;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TaskManagementService
{
  /**
   * Create a new task
   */
  public function createTask(array $data): Task
  {
    return DB::transaction(function () use ($data) {
      $task = Task::create([
        'title' => $data['title'] ?? $data['name'], // Use title as primary, fallback to name
        'name' => $data['name'] ?? $data['title'], // Keep name for backward compatibility
        'description' => $data['description'] ?? null,
        'project_id' => $data['project_id'] ?? null,
        'assigned_to' => $data['assigned_to'] ?? null,
        'assigned_by' => $data['assigned_by'] ?? auth()->id(),
        'status' => $data['status'] ?? TaskStatus::TODO,
        'priority' => $data['priority'] ?? TaskPriority::MEDIUM,
        'estimated_hours' => $data['estimated_hours'] ?? null,
        'due_date' => isset($data['due_date']) ? Carbon::parse($data['due_date']) : null,
        'notes' => $data['notes'] ?? null,
      ]);

      Log::info("Task created", [
        'task_id' => $task->uuid,
        'title' => $task->title,
        'assigned_to' => $task->assigned_to,
        'project_id' => $task->project_id,
      ]);

      return $task;
    });
  }

  /**
   * Update an existing task
   */
  public function updateTask(Task $task, array $data): Task
  {
    return DB::transaction(function () use ($task, $data) {
      $originalStatus = $task->status;

      $task->update([
        'title' => $data['title'] ?? $data['name'] ?? $task->title,
        'name' => $data['name'] ?? $data['title'] ?? $task->name,
        'description' => $data['description'] ?? $task->description,
        'status' => $data['status'] ?? $task->status,
        'priority' => $data['priority'] ?? $task->priority,
        'estimated_hours' => $data['estimated_hours'] ?? $task->estimated_hours,
        'actual_hours' => $data['actual_hours'] ?? $task->actual_hours,
        'due_date' => isset($data['due_date']) ? Carbon::parse($data['due_date']) : $task->due_date,
        'notes' => $data['notes'] ?? $task->notes,
      ]);

      Log::info("Task updated", [
        'task_id' => $task->uuid,
        'changes' => $task->getChanges(),
      ]);

      return $task;
    });
  }

  /**
   * Start working on a task
   */
  public function startTask(Task $task, User $user = null): Task
  {
    $user = $user ?? auth()->user();

    if ($task->status !== TaskStatus::TODO) {
      throw new \Exception("Task must be in TODO status to start");
    }

    $task->update([
      'status' => TaskStatus::IN_PROGRESS,
      'assigned_to' => $user->id,
    ]);

    Log::info("Task started", [
      'task_id' => $task->uuid,
      'user_id' => $user->id,
    ]);

    return $task;
  }

  /**
   * Complete a task
   */
  public function completeTask(Task $task, float $actualHours = null): Task
  {
    if (!in_array($task->status, [TaskStatus::IN_PROGRESS, TaskStatus::REVIEW])) {
      throw new \Exception("Task must be in progress or under review to complete");
    }

    $task->update([
      'status' => TaskStatus::COMPLETED,
      'actual_hours' => $actualHours ?? $task->actual_hours,
    ]);

    Log::info("Task completed", [
      'task_id' => $task->uuid,
      'actual_hours' => $task->actual_hours,
    ]);

    return $task;
  }

  /**
   * Log time spent on a task
   */
  public function logTime(Task $task, float $hours, string $description = null): Task
  {
    $currentHours = $task->actual_hours ?? 0;
    $newTotal = $currentHours + $hours;

    $task->update([
      'actual_hours' => $newTotal,
    ]);

    // If task wasn't started yet, mark it as in progress
    if ($task->status === TaskStatus::TODO) {
      $task->update([
        'status' => TaskStatus::IN_PROGRESS,
      ]);
    }

    Log::info("Time logged for task", [
      'task_id' => $task->uuid,
      'hours_logged' => $hours,
      'total_hours' => $newTotal,
      'description' => $description,
    ]);

    return $task;
  }

  /**
   * Assign task to a user
   */
  public function assignTask(Task $task, User $user, User $assignedBy = null): Task
  {
    $assignedBy = $assignedBy ?? auth()->user();
    $oldAssignee = $task->assigned_to;

    $task->update([
      'assigned_to' => $user->id,
      'assigned_by' => $assignedBy->id,
    ]);

    Log::info("Task reassigned", [
      'task_id' => $task->uuid,
      'from_user' => $oldAssignee,
      'to_user' => $user->id,
      'assigned_by' => $assignedBy->id,
    ]);

    return $task;
  }

  /**
   * Get tasks for a user with filters
   */
  public function getUserTasks(User $user, array $filters = []): Collection
  {
    $query = Task::forUser($user->id)
      ->with(['project.contact.firm', 'assignedUser']);

    // Apply filters using model scopes
    if (isset($filters['status'])) {
      $query->byStatus($filters['status']);
    }

    if (isset($filters['priority'])) {
      $query->byPriority($filters['priority']);
    }

    if (isset($filters['project_id'])) {
      $query->forProject($filters['project_id']);
    }

    if (isset($filters['due_date_from'])) {
      $query->where('due_date', '>=', Carbon::parse($filters['due_date_from']));
    }

    if (isset($filters['due_date_to'])) {
      $query->where('due_date', '<=', Carbon::parse($filters['due_date_to']));
    }

    if (isset($filters['overdue']) && $filters['overdue']) {
      $query->overdue();
    }

    // Default ordering
    $orderBy = $filters['order_by'] ?? 'priority';
    $orderDirection = $filters['order_direction'] ?? 'desc';

    if ($orderBy === 'priority') {
      // Custom priority ordering (urgent > high > medium > low)
      $query->orderByRaw("FIELD(priority, 'urgent', 'high', 'medium', 'low')");
    } else {
      $query->orderBy($orderBy, $orderDirection);
    }

    return $query->get();
  }

  /**
   * Get tasks for a project
   */
  public function getProjectTasks(Project $project, array $filters = []): Collection
  {
    $query = Task::forProject($project->uuid)
      ->with(['assignedUser']);

    // Apply filters using model scopes
    if (isset($filters['status'])) {
      $query->byStatus($filters['status']);
    }

    if (isset($filters['priority'])) {
      $query->byPriority($filters['priority']);
    }

    if (isset($filters['assigned_to'])) {
      $query->forUser($filters['assigned_to']);
    }

    if (isset($filters['overdue']) && $filters['overdue']) {
      $query->overdue();
    }

    // Default ordering by priority then due date
    $query->orderByRaw("FIELD(priority, 'urgent', 'high', 'medium', 'low')")
      ->orderBy('due_date', 'asc');

    return $query->get();
  }

  /**
   * Get overdue tasks for a user (using model scope)
   */
  public function getOverdueTasks(User $user = null): Collection
  {
    $query = Task::overdue()
      ->with(['project.contact.firm', 'assignedUser']);

    if ($user) {
      $query->forUser($user->id);
    }

    return $query->orderBy('due_date', 'asc')->get();
  }

  /**
   * Get task statistics for a user
   */
  public function getUserTaskStats(User $user, Carbon $startDate = null, Carbon $endDate = null): array
  {
    $startDate = $startDate ?? now()->startOfMonth();
    $endDate = $endDate ?? now()->endOfMonth();

    $tasks = Task::forUser($user->id)
      ->whereBetween('created_at', [$startDate, $endDate])
      ->get();

    $completedTasks = $tasks->where('status', TaskStatus::COMPLETED);
    $totalHours = $completedTasks->sum('actual_hours');

    return [
      'total_tasks' => $tasks->count(),
      'completed_tasks' => $completedTasks->count(),
      'in_progress_tasks' => $tasks->where('status', TaskStatus::IN_PROGRESS)->count(),
      'todo_tasks' => $tasks->where('status', TaskStatus::TODO)->count(),
      'overdue_tasks' => $tasks->filter(fn($task) => $task->isOverdue())->count(),
      'total_hours' => $totalHours,
      'average_hours_per_task' => $completedTasks->count() > 0 ? round($totalHours / $completedTasks->count(), 2) : 0,
      'completion_rate' => $tasks->count() > 0 ? round(($completedTasks->count() / $tasks->count()) * 100, 1) : 0,
      'priority_breakdown' => [
        'urgent' => $tasks->where('priority', TaskPriority::URGENT)->count(),
        'high' => $tasks->where('priority', TaskPriority::HIGH)->count(),
        'medium' => $tasks->where('priority', TaskPriority::MEDIUM)->count(),
        'low' => $tasks->where('priority', TaskPriority::LOW)->count(),
      ],
    ];
  }

  /**
   * Get task statistics for a project
   */
  public function getProjectTaskStats(Project $project): array
  {
    $tasks = Task::forProject($project->uuid)->get();
    $completedTasks = $tasks->where('status', TaskStatus::COMPLETED);
    $totalHours = $tasks->sum('actual_hours');
    $estimatedHours = $tasks->sum('estimated_hours');

    return [
      'total_tasks' => $tasks->count(),
      'completed_tasks' => $completedTasks->count(),
      'in_progress_tasks' => $tasks->where('status', TaskStatus::IN_PROGRESS)->count(),
      'todo_tasks' => $tasks->where('status', TaskStatus::TODO)->count(),
      'overdue_tasks' => $tasks->filter(fn($task) => $task->isOverdue())->count(),
      'total_hours' => $totalHours,
      'estimated_hours' => $estimatedHours,
      'completion_rate' => $tasks->count() > 0 ? round(($completedTasks->count() / $tasks->count()) * 100, 1) : 0,
    ];
  }
}
