<?php

namespace App\Policies;

use App\Models\Report;
use App\Models\User;

class ReportPolicy
{
  /**
   * Determine whether the user can view any reports.
   */
  public function viewAny(User $user): bool
  {
    return $user->can('view reports');
  }

  /**
   * Determine whether the user can view the report.
   */
  public function view(User $user, Report $report): bool
  {
    return $user->can('view reports') &&
      ($user->id === $report->generated_by || $user->hasRole('admin'));
  }

  /**
   * Determine whether the user can create reports.
   */
  public function create(User $user): bool
  {
    return $user->can('create reports');
  }

  /**
   * Determine whether the user can update the report.
   */
  public function update(User $user, Report $report): bool
  {
    return $user->can('edit reports') &&
      ($user->id === $report->generated_by || $user->hasRole('admin'));
  }

  /**
   * Determine whether the user can delete the report.
   */
  public function delete(User $user, Report $report): bool
  {
    return $user->can('delete reports') &&
      ($user->id === $report->generated_by || $user->hasRole('admin'));
  }

  /**
   * Determine whether the user can send the report.
   */
  public function send(User $user, Report $report): bool
  {
    return $user->can('send reports') &&
      ($user->id === $report->generated_by || $user->hasRole('admin'));
  }
}
