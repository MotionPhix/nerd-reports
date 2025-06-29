<?php

namespace App\Enums;

enum ReportStatus: string
{
  case DRAFT = 'draft';
  case GENERATING = 'generating';
  case GENERATED = 'generated';
  case SENDING = 'sending';
  case SENT = 'sent';
  case FAILED = 'failed';

  /**
   * Get the label for the report status
   */
  public function getLabel(): string
  {
    return match ($this) {
      self::DRAFT => 'Draft',
      self::GENERATING => 'Generating',
      self::GENERATED => 'Generated',
      self::SENDING => 'Sending',
      self::SENT => 'Sent',
      self::FAILED => 'Failed',
    };
  }

  /**
   * Get the color for the report status (for UI purposes)
   */
  public function getColor(): string
  {
    return match ($this) {
      self::DRAFT => 'gray',
      self::GENERATING => 'blue',
      self::GENERATED => 'green',
      self::SENDING => 'yellow',
      self::SENT => 'green',
      self::FAILED => 'red',
    };
  }

  /**
   * Check if the report is in a processing state
   */
  public function isProcessing(): bool
  {
    return in_array($this, [self::GENERATING, self::SENDING]);
  }

  /**
   * Check if the report is completed
   */
  public function isCompleted(): bool
  {
    return $this === self::SENT;
  }

  /**
   * Check if the report has failed
   */
  public function isFailed(): bool
  {
    return $this === self::FAILED;
  }
}
