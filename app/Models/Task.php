<?php

namespace App\Models;

use App\Enums\TaskStatus;
use App\Enums\TaskPriority;
use App\Traits\HasUuid;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Stevebauman\Purify\Casts\PurifyHtmlOnGet;

class Task extends Model implements HasMedia
{
  use HasFactory, HasUuid, InteractsWithMedia;

  const POSITION_GAP = 60000;

  const POSITION_MIN = 0.00002;

  protected $fillable = [
    'name',
    'description',
    'assigned_to',
    'assigned_by',
    'status',
    'board_id',
    'position',
    'priority',
    'estimated_hours',
    'actual_hours',
    'started_at',
    'completed_at',
    'due_date',
    'notes'
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

  public function board(): BelongsTo
  {
    return $this->belongsTo(Board::class, 'board_id', 'uuid');
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

  public static function booted(): void
  {
    static::creating(function ($task) {

      $task->tid = Str::orderedUuid();

      $task->position = self::query()->where('board_id', $task->board_id)
          ->orderByDesc('position')
          ->first()?->position + self::POSITION_GAP;

    });

    static::updating(function ($task) {

      if (!isset($task->tid)) {

        $task->tid = Str::orderedUuid();

      }

    });

    static::saved(function ($task) {

      if ($task->position < self::POSITION_MIN) {

        $previousPosition = 0;

        $tasks = DB::table('tasks')
          ->select('id')
          ->where('board_id', $task->board_id)
          ->orderBy('position')
          ->get();

        foreach ($tasks as $task) {
          DB::table('tasks')
            ->where('id', $task->id)
            ->update(['position' => $previousPosition += self::POSITION_GAP]);
        }
      }
    });
  }
}
