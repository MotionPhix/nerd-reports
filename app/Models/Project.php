<?php

namespace App\Models;

use App\Enums\ProjectStatus;
use App\Traits\HasUuid;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Stevebauman\Purify\Casts\PurifyHtmlOnGet;

class Project extends Model
{
  use HasFactory, HasUuid;

  protected $fillable = [
    'name',
    'description',
    'due_date',
    'deadline',
    'created_by',
    'contact_id',
    'status',
    'priority',
    'estimated_hours',
    'budget',
    'hourly_rate',
    'is_billable',
    'notes',
  ];

  protected $appends = ['deadline_human'];

  protected $primaryKey = 'uuid';

  public $incrementing = false;

  protected $keyType = 'string';

  protected function casts(): array
  {
    return [
      'due_date' => 'date:j F, Y',
      'deadline' => 'date:j F, Y',
      'description' => PurifyHtmlOnGet::class,
      'name' => PurifyHtmlOnGet::class,
      'status' => ProjectStatus::class,
      'is_billable' => 'boolean',
      'estimated_hours' => 'decimal:2',
      'budget' => 'decimal:2',
      'hourly_rate' => 'decimal:2',
    ];
  }

  protected function createdAt(): Attribute
  {
    return Attribute::make(
      get: fn ($value) => Carbon::createFromTimeString($value)->format('j F, Y')
    );
  }

  protected function deadlineHuman(): Attribute
  {
    return Attribute::make(
      get: fn () => $this->deadline?->diffForHumans()
    );
  }

  public function contact(): BelongsTo
  {
    return $this->belongsTo(Contact::class, 'contact_id', 'uuid');
  }

  public function firm()
  {
    return $this->hasOneThrough(Firm::class, Contact::class, 'uuid', 'uuid', 'contact_id', 'firm_id');
  }

  public function author(): BelongsTo
  {
    return $this->belongsTo(User::class, 'created_by');
  }

  public function tasks(): HasMany
  {
    return $this->hasMany(Task::class, 'project_id', 'uuid');
  }

  public function interactions(): HasMany
  {
    return $this->hasMany(Interaction::class, 'project_id', 'uuid');
  }

  public function users(): Attribute
  {
    return Attribute::make(
      get: fn () => DB::table('users')
        ->select(['users.id', 'users.first_name', 'users.last_name'])
        ->where('users.id', '!=', auth()->id())
        ->orderBy('users.first_name')
        ->orderBy('users.last_name')
        ->get()
    );
  }

  // Reporting helper methods
  public function getTasksWorkedOnInWeek(Carbon $startOfWeek, Carbon $endOfWeek, $userId = null)
  {
    $query = $this->tasks()->workedOnInWeek($startOfWeek, $endOfWeek);

    if ($userId) {
      $query->forUser($userId);
    }

    return $query->get();
  }

  public function getCompletedTasksInWeek(Carbon $startOfWeek, Carbon $endOfWeek, $userId = null)
  {
    $query = $this->tasks()->completedInWeek($startOfWeek, $endOfWeek);

    if ($userId) {
      $query->forUser($userId);
    }

    return $query->get();
  }

  public function getTotalHoursInWeek(Carbon $startOfWeek, Carbon $endOfWeek, $userId = null)
  {
    $query = $this->tasks()->workedOnInWeek($startOfWeek, $endOfWeek);

    if ($userId) {
      $query->forUser($userId);
    }

    return $query->sum('actual_hours') ?? 0;
  }

  public function hasActivityInWeek(Carbon $startOfWeek, Carbon $endOfWeek, $userId = null): bool
  {
    return $this->getTasksWorkedOnInWeek($startOfWeek, $endOfWeek, $userId)->isNotEmpty();
  }

  public function scopeWithActivityInWeek($query, Carbon $startOfWeek, Carbon $endOfWeek, $userId = null)
  {
    return $query->whereHas('tasks', function ($taskQuery) use ($startOfWeek, $endOfWeek, $userId) {
      $taskQuery->workedOnInWeek($startOfWeek, $endOfWeek);
      if ($userId) {
        $taskQuery->forUser($userId);
      }
    });
  }

  // Project status helpers
  public function isActive(): bool
  {
    return in_array($this->status, ['in_progress', 'approved']);
  }

  public function isCompleted(): bool
  {
    return $this->status === 'completed';
  }

  public function isOverdue(): bool
  {
    return $this->due_date && $this->due_date->isPast() && !$this->isCompleted();
  }

  // Project progress calculation
  public function getProgress(): array
  {
    $totalTasks = $this->tasks()->count();
    $completedTasks = $this->tasks()->where('status', 'completed')->count();

    $percentage = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;

    return [
      'percentage' => $percentage,
      'completed_tasks' => $completedTasks,
      'total_tasks' => $totalTasks,
      'status' => $this->getProgressStatus($percentage),
    ];
  }

  private function getProgressStatus(int $percentage): string
  {
    if ($percentage === 100) return 'completed';
    if ($percentage >= 75) return 'nearly_done';
    if ($percentage >= 50) return 'on_track';
    if ($percentage >= 25) return 'started';
    return 'not_started';
  }

  // Project statistics
  public function getStats(): array
  {
    $tasks = $this->tasks;

    return [
      'total_tasks' => $tasks->count(),
      'completed_tasks' => $tasks->where('status', 'completed')->count(),
      'in_progress_tasks' => $tasks->where('status', 'in_progress')->count(),
      'todo_tasks' => $tasks->where('status', 'todo')->count(),
      'overdue_tasks' => $tasks->filter(function ($task) {
        return $task->due_date && $task->due_date->isPast() && $task->status !== 'completed';
      })->count(),
      'total_hours' => $tasks->sum('actual_hours') ?? 0,
      'estimated_hours' => $tasks->sum('estimated_hours') ?? 0,
      'completion_rate' => $this->getProgress()['percentage'],
      'team_members' => $tasks->whereNotNull('assigned_to')->pluck('assigned_to')->unique()->count(),
    ];
  }
}
