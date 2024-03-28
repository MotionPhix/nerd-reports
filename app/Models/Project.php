<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    protected $casts = [
      'due_date' => 'date:j F, Y',
      'created_at' => 'date:j F, Y',
    ];

    public function contact(): BelongsTo
    {
      return $this->belongsTo(Contact::class);
    }

    public function firm(): BelongsTo
    {
      return $this->contact->firm();
    }

    public function owner(): BelongsTo
    {
      return $this->belongsTo(User::class);
    }

    public function boards(): HasMany
    {
      return $this->hasMany(Board::class);
    }

    public function tasks(): HasMany
    {
      return $this->hasMany(Task::class);
    }

    public function files()
    {
      return $this->hasMany(\App\Models\File::class);
    }

    public function getAssignableUsersAttribute()
    {
      $loggedInUserId = auth()->id();

      // $excludedRoles = ['admin', 'general-manager'];

      $users = \DB::table('users')
        ->select(['users.id', 'users.name'])
        // ->leftJoin('role_user', 'users.id', '=', 'role_user.user_id')
        // ->leftJoin('roles', 'role_user.role_id', '=', 'roles.id')
        ->where('users.id', '<>', $loggedInUserId)
        // ->whereNotIn('roles.slug', $excludedRoles)
        ->orderBy('users.name')
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
