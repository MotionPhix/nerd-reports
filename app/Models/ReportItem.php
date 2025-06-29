<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportItem extends Model
{
  use HasFactory, HasUuid;

  protected $fillable = [
    'report_id',
    'project_id',
    'project_name',
    'contact_name',
    'firm_name',
    'total_hours',
    'task_count',
    'completed_task_count',
    'tasks_data',
    'notes'
  ];

  protected $primaryKey = 'uuid';
  public $incrementing = false;
  protected $keyType = 'string';

  protected function casts(): array
  {
    return [
      'total_hours' => 'decimal:2',
      'tasks_data' => 'array',
    ];
  }

  public function report(): BelongsTo
  {
    return $this->belongsTo(Report::class);
  }

  public function project(): BelongsTo
  {
    return $this->belongsTo(Project::class);
  }

  // Helper methods
  public function getCompletionPercentage(): float
  {
    if ($this->task_count === 0) {
      return 0;
    }

    return round(($this->completed_task_count / $this->task_count) * 100, 1);
  }

  public function getFormattedHours(): string
  {
    if (!$this->total_hours) {
      return 'No time logged';
    }

    $hours = floor($this->total_hours);
    $minutes = ($this->total_hours - $hours) * 60;

    if ($hours > 0 && $minutes > 0) {
      return "{$hours}h {$minutes}m";
    } elseif ($hours > 0) {
      return "{$hours}h";
    } else {
      return "{$minutes}m";
    }
  }

  public function getTasksSummary(): array
  {
    $tasks = $this->tasks_data ?? [];

    return [
      'total' => count($tasks),
      'completed' => collect($tasks)->where('status', 'completed')->count(),
      'in_progress' => collect($tasks)->where('status', 'in_progress')->count(),
      'todo' => collect($tasks)->where('status', 'todo')->count(),
      'other' => collect($tasks)->whereNotIn('status', ['completed', 'in_progress', 'todo'])->count(),
    ];
  }
}
