<?php

namespace App\Enums;

enum ProjectStatus: string
{
  case PENDING = 'in_progress';

  case APPROVED = 'approved';

  case COMPLETED = 'completed';

  case CANCELLED = 'cancelled';

  /**
   * @return string
   */
  public function getLabel(): string
  {
    return match ($this) {

      self::PENDING => 'Being worked on',

      self::APPROVED => 'Waiting approval',

      self::COMPLETED => 'Ready for production',

      self::CANCELLED => 'project cancelled',

    };
  }
}
