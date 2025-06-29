<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
  /**
   * Determine whether the user can view any tasks.
   */
  public function viewAny(User $user): bool
  {
    return $user->can('view tasks');
  }

  /**
   * Determine whether the user can view the task.
   */
  public function view(User $user, Task $task): bool
  {
    return $user->can('view tasks') &&
      ($user->id === $task->assigned_to ||
        $user->id === $task->assigned_by ||
        $user->hasRole(['admin', 'project-manager']));
  }

  /**
   * Determine whether the user can create tasks.
   */
  public function create(User $user): bool
  {
    return $user->can('create tasks');
  }

  /**
   * Determine whether the user can update the task.
   */
  public function update(User $user, Task $task): bool
  {
    return $user->can('edit tasks') &&
      ($user->id === $task->assigned_to ||
        $user->id === $task->assigned_by ||
        $user->hasRole(['admin', 'project-manager']));
  }

  /**
   * Determine whether the user can delete the task.
   */
  public function delete(User $user, Task $task): bool
  {
    return $user->can('delete tasks') &&
      ($user->id === $task->assigned_by ||
        $user->hasRole(['admin', 'project-manager']));
  }

  /**
   * Determine whether the user can assign the task.
   */
  public function assign(User $user, Task $task): bool
  {
    return $user->can('assign tasks') &&
      ($user->id === $task->assigned_by ||
        $user->hasRole(['admin', 'project-manager']));
  }

  /**
   * Determine whether the user can complete the task.
   */
  public function complete(User $user, Task $task): bool
  {
    return $user->can('complete tasks') &&
      ($user->id === $task->assigned_to ||
        $user->hasRole(['admin', 'project-manager']));
  }
}
