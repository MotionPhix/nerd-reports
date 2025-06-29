<?php

namespace App\Enums;

enum ReportType: string
{
  case WEEKLY = 'weekly';
  case CUSTOM = 'custom';
  case MONTHLY = 'monthly';
  case PROJECT_SPECIFIC = 'project_specific';
  case CLIENT_SPECIFIC = 'client_specific';

  /**
   * Get the label for the report type
   */
  public function getLabel(): string
  {
    return match ($this) {
      self::WEEKLY => 'Weekly Report',
      self::CUSTOM => 'Custom Date Range',
      self::MONTHLY => 'Monthly Report',
      self::PROJECT_SPECIFIC => 'Project-Specific Report',
      self::CLIENT_SPECIFIC => 'Client-Specific Report',
    };
  }

  /**
   * Get the description for the report type
   */
  public function getDescription(): string
  {
    return match ($this) {
      self::WEEKLY => 'Automated weekly report sent every Friday',
      self::CUSTOM => 'Custom report for a specific date range',
      self::MONTHLY => 'Monthly summary report',
      self::PROJECT_SPECIFIC => 'Report focused on a specific project',
      self::CLIENT_SPECIFIC => 'Report focused on a specific client/firm',
    };
  }

  /**
   * Check if this report type is automated
   */
  public function isAutomated(): bool
  {
    return in_array($this, [self::WEEKLY, self::MONTHLY]);
  }

  /**
   * Check if this report type requires manual generation
   */
  public function isManual(): bool
  {
    return !$this->isAutomated();
  }
}
