<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
  /**
   * Determine whether the user can view any projects.
   */
  public function viewAny(User $user): bool
  {
    return $user->can('view projects');
  }

  /**
   * Determine whether the user can view the project.
   */
  public function view(User $user, Project $project): bool
  {
    return $user->can('view projects') &&
      ($user->id === $project->created_by ||
        $project->tasks()->where('assigned_to', $user->id)->exists() ||
        $user->hasRole(['admin', 'project-manager']));
  }

  /**
   * Determine whether the user can create projects.
   */
  public function create(User $user): bool
  {
    return $user->can('create projects');
  }

  /**
   * Determine whether the user can update the project.
   */
  public function update(User $user, Project $project): bool
  {
    return $user->can('edit projects') &&
      ($user->id === $project->created_by ||
        $user->hasRole(['admin', 'project-manager']));
  }

  /**
   * Determine whether the user can delete the project.
   */
  public function delete(User $user, Project $project): bool
  {
    return $user->can('delete projects') &&
      ($user->id === $project->created_by ||
        $user->hasRole(['admin', 'project-manager']));
  }

  /**
   * Determine whether the user can manage project members.
   */
  public function manageMembers(User $user, Project $project): bool
  {
    return $user->can('manage project members') &&
      ($user->id === $project->created_by ||
        $user->hasRole(['admin', 'project-manager']));
  }
}
