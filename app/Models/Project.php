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
use Stevebauman\Purify\Casts\PurifyHtmlOnGet;
use Illuminate\Support\Facades\DB;

/**
 * @property ProjectStatus $status
 * @property PurifyHtmlOnGet $description
 * @property PurifyHtmlOnGet $name
 * @property Carbon $due_date
 */
class Project extends Model
{
  use HasFactory, HasUuid;

  protected $fillable = [
    'name',
    'description',
    'due_date',
    'created_by',
    'contact_id',
    'status',
  ];

  protected $appends = ['deadline'];

  protected $primaryKey = 'uuid';

  public $incrementing = false;

  protected $keyType = 'string';

  protected function casts(): array
  {
    return [
      'due_date' => 'date:j F, Y',
      'description' => PurifyHtmlOnGet::class,
      'name' => PurifyHtmlOnGet::class,
      'status' => ProjectStatus::class,
    ];
  }

  protected function createdAt(): Attribute
  {
    return Attribute::make(
      get: fn ($value) => Carbon::createFromTimeString($value)->format('j F, Y')
    );
  }

  protected function deadline(): Attribute
  {
    return Attribute::make(
      get: fn () => $this->due_date?->diffForHumans()
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

  public function boards(): HasMany
  {
    return $this->hasMany(Board::class, 'project_id', 'uuid');
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

  public function scopeTransferOwnershipTo($newOwnerId)
  {
    $currentOwner = $this->owner->first();

    // Check if the current owner has any tasks on the project
    $hasTasks = $this->tasks()->where('user_id', $currentOwner->id)->exists();

    // If the current owner doesn't have tasks, remove them from the project_user table
    if (!$hasTasks && $currentOwner) {
      $this->users()->detach($currentOwner);
    }

    // Update the new owner's role to 'owner'
    $this->users()->syncWithoutDetaching([
      $newOwnerId => ['role' => 'owner'],
    ]);
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
}
