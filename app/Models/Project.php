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

  // Relationships
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

  // Scopes for filtering
  public function scopeByStatus($query, $status)
  {
    if (empty($status)) {
      return $query;
    }

    // Handle both enum instances and string values
    if ($status instanceof ProjectStatus) {
      return $query->where('status', $status->value);
    }

    // Handle string values - convert to enum value if needed
    $enumValue = ProjectStatus::tryFrom($status);
    if ($enumValue) {
      return $query->where('status', $enumValue->value);
    }

    // Fallback to direct string comparison
    return $query->where('status', $status);
  }

  public function scopeByContact($query, $contactId)
  {
    if (empty($contactId)) {
      return $query;
    }

    return $query->where('contact_id', $contactId);
  }

  public function scopeByFirm($query, $firmId)
  {
    if (empty($firmId)) {
      return $query;
    }

    return $query->whereHas('contact', function ($q) use ($firmId) {
      $q->where('firm_id', $firmId);
    });
  }

  public function scopeByDateRange($query, $startDate = null, $endDate = null)
  {
    if ($startDate) {
      $query->where('due_date', '>=', Carbon::parse($startDate));
    }

    if ($endDate) {
      $query->where('due_date', '<=', Carbon::parse($endDate));
    }

    return $query;
  }

  public function scopeOverdue($query)
  {
    return $query->where('due_date', '<', now())
      ->whereNotIn('status', [ProjectStatus::COMPLETED->value, ProjectStatus::CANCELLED->value]);
  }

  public function scopeActive($query)
  {
    return $query->whereIn('status', [ProjectStatus::PENDING->value, ProjectStatus::APPROVED->value]);
  }

  public function scopeCompleted($query)
  {
    return $query->where('status', ProjectStatus::COMPLETED->value);
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

  public function scopeSearch($query, $search)
  {
    if (empty($search)) {
      return $query;
    }

    return $query->where(function ($q) use ($search) {
      $q->where('name', 'like', "%{$search}%")
        ->orWhere('description', 'like', "%{$search}%")
        ->orWhereHas('contact', function ($contactQuery) use ($search) {
          $contactQuery->where('first_name', 'like', "%{$search}%")
            ->orWhere('last_name', 'like', "%{$search}%")
            ->orWhereRaw("CONCAT(first_name, ' ', last_name) like ?", ["%{$search}%"]);
        })
        ->orWhereHas('contact.firm', function ($firmQuery) use ($search) {
          $firmQuery->where('name', 'like', "%{$search}%");
        });
    });
  }

  // Simple helper methods (keep these as they're model-specific)
  public function isActive(): bool
  {
    return in_array($this->status, [ProjectStatus::PENDING, ProjectStatus::APPROVED]);
  }

  public function isCompleted(): bool
  {
    return $this->status === ProjectStatus::COMPLETED;
  }

  public function isOverdue(): bool
  {
    return $this->due_date && $this->due_date->isPast() && !$this->isCompleted();
  }

  // Reporting helper methods for weekly reports
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
}
