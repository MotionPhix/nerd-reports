<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Stevebauman\Purify\Casts\PurifyHtmlOnGet;

class Project extends Model
{
  use HasFactory;

  protected $fillable = [
    'name',
    'description',
    'due_date',
    'created_by',
    'contact_id',
    'status',
  ];

  protected function casts(): array
  {
    return [
      'due_date' => 'date:j F, Y',
      'description' => PurifyHtmlOnGet::class,
    ];
  }

  protected function createdAt(): Attribute
  {
    return Attribute::make(
      get: fn ($value) => Carbon::createFromTimeString($value)->format('j F, Y')
    );
  }

  public function contact(): BelongsTo
  {
    return $this->belongsTo(Contact::class);
  }

  public function firm(): BelongsTo
  {
    return $this->contact->firm();
  }

  public function author(): BelongsTo
  {
    return $this->belongsTo(User::class, 'created_by');
  }

  public function boards(): HasMany
  {
    return $this->hasMany(Board::class);
  }

  public function files()
  {
    return $this->hasMany(\App\Models\File::class);
  }

  public function getUsersAttribute()
  {
    $loggedInUserId = auth()->id();

    $users = \DB::table('users')
      ->select(['users.id', 'users.first_name', 'users.last_name'])
      ->where('users.id', '<>', $loggedInUserId)
      ->orderBy('users.first_name')
      ->orderBy('users.last_name')
      ->groupBy('users.id')
      ->get();

    return $users;
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

  protected static function boot()
  {
    parent::boot();

    static::creating(function ($project) {
      $project->pid = Str::orderedUuid();
    });

    static::updating(function ($project) {
      if (!isset($project->pid)) {
        $project->pid = Str::orderedUuid();
      }
    });
  }
}
