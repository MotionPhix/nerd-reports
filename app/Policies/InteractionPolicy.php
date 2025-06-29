<?php

namespace App\Policies;

use App\Models\Interaction;
use App\Models\User;

class InteractionPolicy
{
  /**
   * Determine whether the user can view any interactions.
   */
  public function viewAny(User $user): bool
  {
    return $user->can('view interactions');
  }

  /**
   * Determine whether the user can view the interaction.
   */
  public function view(User $user, Interaction $interaction): bool
  {
    return $user->can('view interactions') &&
      ($user->id === $interaction->user_id ||
        $user->hasRole(['admin', 'project-manager']));
  }

  /**
   * Determine whether the user can create interactions.
   */
  public function create(User $user): bool
  {
    return $user->can('create interactions');
  }

  /**
   * Determine whether the user can update the interaction.
   */
  public function update(User $user, Interaction $interaction): bool
  {
    return $user->can('edit interactions') &&
      ($user->id === $interaction->user_id ||
        $user->hasRole(['admin', 'project-manager']));
  }

  /**
   * Determine whether the user can delete the interaction.
   */
  public function delete(User $user, Interaction $interaction): bool
  {
    return $user->can('delete interactions') &&
      ($user->id === $interaction->user_id ||
        $user->hasRole(['admin', 'project-manager']));
  }
}
