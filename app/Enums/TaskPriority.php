<?php

namespace App\Enums;

enum TaskPriority: string
{
  case LOW = 'low';
  case MEDIUM = 'medium';
  case HIGH = 'high';
  case URGENT = 'urgent';

  /**
   * Get the label for the task priority
   */
  public function getLabel(): string
  {
    return match ($this) {
      self::LOW => 'Low Priority',
      self::MEDIUM => 'Medium Priority',
      self::HIGH => 'High Priority',
      self::URGENT => 'Urgent',
    };
  }

  /**
   * Get the color for the task priority (for UI purposes)
   */
  public function getColor(): string
  {
    return match ($this) {
      self::LOW => 'green',
      self::MEDIUM => 'yellow',
      self::HIGH => 'orange',
      self::URGENT => 'red',
    };
  }

  /**
   * Get the numeric value for sorting purposes
   */
  public function getValue(): int
  {
    return match ($this) {
      self::LOW => 1,
      self::MEDIUM => 2,
      self::HIGH => 3,
      self::URGENT => 4,
    };
  }

  /**
   * Get all priorities ordered by value
   */
  public static function ordered(): array
  {
    return [
      self::URGENT,
      self::HIGH,
      self::MEDIUM,
      self::LOW,
    ];
  }
}
