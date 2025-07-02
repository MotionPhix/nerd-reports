<?php

namespace App\Services;

use App\Enums\TaskStatus;
use App\Enums\TaskPriority;
use App\Models\Board;
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
        'name' => $data['name'],
        'description' => $data['description'] ?? null,
        'board_id' => $data['board_id'],
        'project_id' => $data['project_id'] ?? null,
        'assigned_to' => $data['assigned_to'],
        'assigned_by' => $data['assigned_by'] ?? auth()->id(),
        'status' => $data['status'] ?? TaskStatus::TODO,
        'priority' => $data['priority'] ?? TaskPriority::MEDIUM,
        'estimated_hours' => $data['estimated_hours'] ?? null,
        'due_date' => isset($data['due_date']) ? Carbon::parse($data['due_date']) : null,
        'notes' => $data['notes'] ?? null,
      ]);

      Log::info("Task created", [
        'task_id' => $task->uuid,
        'name' => $task->name,
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
        'name' => $data['name'] ?? $task->name,
        'description' => $data['description'] ?? $task->description,
        'status' => $data['status'] ?? $task->status,
        'priority' => $data['priority'] ?? $task->priority,
        'estimated_hours' => $data['estimated_hours'] ?? $task->estimated_hours,
        'actual_hours' => $data['actual_hours'] ?? $task->actual_hours,
        'due_date' => isset($data['due_date']) ? Carbon::parse($data['due_date']) : $task->due_date,
        'notes' => $data['notes'] ?? $task->notes,
      ]);

      // Handle status changes
      $this->handleStatusChange($task, $originalStatus, $task->status);

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
      'started_at' => now(),
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
      'completed_at' => now(),
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
        'started_at' => now(),
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
   * Move task to different board
   */
  public function moveTask(Task $task, Board $targetBoard, float $position = null): Task
  {
    $oldBoardId = $task->board_id;

    $task->update([
      'board_id' => $targetBoard->uuid,
      'position' => $position ?? $this->getNextPosition($targetBoard),
    ]);

    Log::info("Task moved between boards", [
      'task_id' => $task->uuid,
      'from_board' => $oldBoardId,
      'to_board' => $targetBoard->uuid,
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
    $query = Task::where('assigned_to', $user->id)
      ->with(['project.contact.firm']);

    // Apply filters
    if (isset($filters['status'])) {
      $query->where('status', $filters['status']);
    }

    if (isset($filters['priority'])) {
      $query->where('priority', $filters['priority']);
    }

    if (isset($filters['project_id'])) {
      $query->where('project_id', $filters['project_id']);
    }

    if (isset($filters['due_date_from'])) {
      $query->where('due_date', '>=', Carbon::parse($filters['due_date_from']));
    }

    if (isset($filters['due_date_to'])) {
      $query->where('due_date', '<=', Carbon::parse($filters['due_date_to']));
    }

    if (isset($filters['overdue']) && $filters['overdue']) {
      $query->where('due_date', '<', now())
        ->whereNotIn('status', [TaskStatus::COMPLETED, TaskStatus::CANCELLED]);
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
   * Get overdue tasks for a user
   */
  public function getOverdueTasks(User $user = null): Collection
  {
    $query = Task::where('due_date', '<', now())
      ->whereNotIn('status', [TaskStatus::COMPLETED, TaskStatus::CANCELLED])
      ->with(['project.contact.firm', 'user']);

    if ($user) {
      $query->where('assigned_to', $user->id);
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

    $tasks = Task::where('assigned_to', $user->id)
      ->whereBetween('created_at', [$startDate, $endDate])
      ->get();

    $completedTasks = $tasks->where('status', TaskStatus::COMPLETED);
    $totalHours = $completedTasks->sum('actual_hours');

    return [
      'total_tasks' => $tasks->count(),
      'completed_tasks' => $completedTasks->count(),
      'in_progress_tasks' => $tasks->where('status', TaskStatus::IN_PROGRESS)->count(),
      'todo_tasks' => $tasks->where('status', TaskStatus::TODO)->count(),
      'overdue_tasks' => $tasks->where('due_date', '<', now())
        ->whereNotIn('status', [TaskStatus::COMPLETED, TaskStatus::CANCELLED])
        ->count(),
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
   * Handle status changes and update timestamps
   */
  private function handleStatusChange(Task $task, TaskStatus $oldStatus, TaskStatus $newStatus): void
  {
    // If moving to in progress and wasn't started
    if ($newStatus === TaskStatus::IN_PROGRESS && !$task->started_at) {
      $task->update(['started_at' => now()]);
    }

    // If completing task
    if ($newStatus === TaskStatus::COMPLETED && $oldStatus !== TaskStatus::COMPLETED) {
      $task->update(['completed_at' => now()]);
    }

    // If reopening completed task
    if ($oldStatus === TaskStatus::COMPLETED && $newStatus !== TaskStatus::COMPLETED) {
      $task->update(['completed_at' => null]);
    }
  }

  /**
   * Get next position for a board
   */
  private function getNextPosition(Board $board): float
  {
    $lastTask = Task::where('board_id', $board->uuid)
      ->orderByDesc('position')
      ->first();

    return ($lastTask?->position ?? 0) + Task::POSITION_GAP;
  }

  /**
   * Bulk update task positions (for drag and drop)
   */
  public function updateTaskPositions(array $taskPositions): void
  {
    DB::transaction(function () use ($taskPositions) {
      foreach ($taskPositions as $taskData) {
        Task::where('uuid', $taskData['id'])
          ->update([
            'position' => $taskData['position'],
            'board_id' => $taskData['board_id'] ?? null,
          ]);
      }
    });
  }
}
