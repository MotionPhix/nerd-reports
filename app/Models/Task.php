<?php

namespace App\Models;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Traits\HasUuid;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Stevebauman\Purify\Casts\PurifyHtmlOnGet;

class Task extends Model implements HasMedia
{
  use HasFactory, HasUuid, InteractsWithMedia;

  protected $fillable = [
    'name',
    'title', // Alternative to name
    'description',
    'assigned_to',
    'assigned_by',
    'status',
    'project_id',
    'priority',
    'estimated_hours',
    'actual_hours',
    'started_at',
    'completed_at',
    'due_date',
    'notes',
  ];

  protected function casts(): array
  {
    return [
      'created_at' => 'datetime',
      'updated_at' => 'datetime',
      'started_at' => 'datetime',
      'completed_at' => 'datetime',
      'due_date' => 'datetime',
      'description' => PurifyHtmlOnGet::class,
      'name' => PurifyHtmlOnGet::class,
      'title' => PurifyHtmlOnGet::class,
      'status' => TaskStatus::class,
      'priority' => TaskPriority::class,
      'estimated_hours' => 'decimal:2',
      'actual_hours' => 'decimal:2',
    ];
  }

  protected function createdAt(): Attribute
  {
    return Attribute::make(
      get: fn($value) => Carbon::parse($value)->diffForHumans(),
    );
  }

  // Get task title (fallback to name if title is null)
  protected function title(): Attribute
  {
    return Attribute::make(
      get: fn($value) => $value ?? $this->name,
    );
  }

  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class, 'assigned_to');
  }

  public function assignee(): BelongsTo
  {
    return $this->belongsTo(User::class, 'assigned_by');
  }

  public function comments(): HasMany
  {
    return $this->hasMany(Comment::class);
  }

  public function project(): BelongsTo
  {
    return $this->belongsTo(Project::class, 'project_id', 'uuid');
  }

  // Reporting helper methods
  public function isCompleted(): bool
  {
    return $this->status === TaskStatus::COMPLETED;
  }

  public function isInProgress(): bool
  {
    return $this->status === TaskStatus::IN_PROGRESS;
  }

  public function isOverdue(): bool
  {
    return $this->due_date && $this->due_date->isPast() && !$this->isCompleted();
  }

  public function getTimeSpent(): ?float
  {
    return $this->actual_hours;
  }

  public function getFormattedTimeSpent(): string
  {
    if (!$this->actual_hours) {
      return 'No time logged';
    }

    $hours = floor($this->actual_hours);
    $minutes = ($this->actual_hours - $hours) * 60;

    if ($hours > 0 && $minutes > 0) {
      return "{$hours}h {$minutes}m";
    } elseif ($hours > 0) {
      return "{$hours}h";
    } else {
      return "{$minutes}m";
    }
  }

  public function scopeCompletedInWeek($query, Carbon $startOfWeek, Carbon $endOfWeek)
  {
    return $query->where('status', TaskStatus::COMPLETED)
      ->whereBetween('completed_at', [$startOfWeek, $endOfWeek]);
  }

  public function scopeWorkedOnInWeek($query, Carbon $startOfWeek, Carbon $endOfWeek)
  {
    return $query->where(function ($q) use ($startOfWeek, $endOfWeek) {
      $q->whereBetween('created_at', [$startOfWeek, $endOfWeek])
        ->orWhereBetween('updated_at', [$startOfWeek, $endOfWeek])
        ->orWhereBetween('completed_at', [$startOfWeek, $endOfWeek]);
    });
  }

  public function scopeForUser($query, $userId)
  {
    return $query->where('assigned_to', $userId);
  }

  public function scopeForProject($query, $projectId)
  {
    return $query->where('project_id', $projectId);
  }

  public function scopeOverdue($query)
  {
    return $query->whereNotNull('due_date')
      ->where('due_date', '<', now())
      ->whereNotIn('status', [TaskStatus::COMPLETED]);
  }

  public function scopeByStatus($query, $status)
  {
    return $query->where('status', $status);
  }

  public function scopeByPriority($query, $priority)
  {
    return $query->where('priority', $priority);
  }

  // Auto-complete task when marked as completed
  public static function booted(): void
  {
    static::updating(function ($task) {
      if ($task->isDirty('status') && $task->status === TaskStatus::COMPLETED) {
        $task->completed_at = now();
      }

      if ($task->isDirty('status') && $task->status === TaskStatus::IN_PROGRESS && !$task->started_at) {
        $task->started_at = now();
      }
    });
  }
}
