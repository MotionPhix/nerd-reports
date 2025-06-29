<?php

namespace App\Enums;

enum TaskStatus: string
{
  case TODO = 'todo';
  case IN_PROGRESS = 'in_progress';
  case COMPLETED = 'completed';
  case CANCELLED = 'cancelled';
  case ON_HOLD = 'on_hold';
  case REVIEW = 'review';

  /**
   * Get the label for the task status
   */
  public function getLabel(): string
  {
    return match ($this) {
      self::TODO => 'To Do',
      self::IN_PROGRESS => 'In Progress',
      self::COMPLETED => 'Completed',
      self::CANCELLED => 'Cancelled',
      self::ON_HOLD => 'On Hold',
      self::REVIEW => 'Under Review',
    };
  }

  /**
   * Get the color for the task status (for UI purposes)
   */
  public function getColor(): string
  {
    return match ($this) {
      self::TODO => 'gray',
      self::IN_PROGRESS => 'blue',
      self::COMPLETED => 'green',
      self::CANCELLED => 'red',
      self::ON_HOLD => 'yellow',
      self::REVIEW => 'purple',
    };
  }

  /**
   * Check if the task is considered "active" (being worked on)
   */
  public function isActive(): bool
  {
    return in_array($this, [self::TODO, self::IN_PROGRESS, self::REVIEW]);
  }

  /**
   * Check if the task is considered "finished" (no more work needed)
   */
  public function isFinished(): bool
  {
    return in_array($this, [self::COMPLETED, self::CANCELLED]);
  }
}
