<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Contracts\auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail, HasMedia
{
  use HasFactory, Notifiable, HasApiTokens, HasUuid, HasRoles, InteractsWithMedia;

  /**
   * The attributes that are mass assignable.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    'first_name',
    'last_name',
    'email',
    'password',
  ];

  protected $primaryKey = 'uuid';

  protected $keyType = 'string';

  public $incrementing = false;

  /**
   * The attributes that should be hidden for serialization.
   *
   * @var array<int, string>
   */
  protected $hidden = [
    'password',
    'remember_token',
  ];

  /**
   * Get the attributes that should be cast.
   *
   * @return array<string, string>
   */
  protected function casts(): array
  {
    return [
      'email_verified_at' => 'datetime',
      'password' => 'hashed',
    ];
  }

  public function name(): Attribute
  {
    return Attribute::make(
      get: fn() => "{$this->first_name} {$this->last_name}",
    )->shouldCache();
  }

  public function avatar(): MorphOne
  {
    return $this->morphOne(Media::class, 'model')->where('collection_name', 'avatar');
  }

  public function comments(): HasMany
  {
    return $this->hasMany(Comment::class);
  }

  public function replies(): HasMany
  {
    return $this->hasMany(Reply::class);
  }

  public function assignedTasks(): HasMany
  {
    return $this->hasMany(Task::class, 'assigned_to');
  }

  public function createdTasks(): HasMany
  {
    return $this->hasMany(Task::class, 'assigned_by');
  }

  public function createdProjects(): HasMany
  {
    return $this->hasMany(Project::class, 'created_by');
  }

  public function generatedReports(): HasMany
  {
    return $this->hasMany(Report::class, 'generated_by');
  }

  public function interactions(): HasMany
  {
    return $this->hasMany(Interaction::class, 'user_id');
  }

  public function avatarUrl(): Attribute
  {
    return Attribute::make(
      get: fn() => 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($this->email)))
    ); // TODO: check on the internal avatar relation to return that URL if it exists
  }
}
