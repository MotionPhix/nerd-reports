<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'role',
        'email',
        'password',
    ];

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
            get: fn () => "{$this->first_name} {$this->last_name}",
        )->shouldCache();
    }

    public function images(): MorphMany
    {
      return $this->morphMany(File::class, 'model');
    }

    public function comments(): HasMany
    {
      return $this->hasMany(Comment::class);
    }

    public function latestImage(): MorphOne
    {
      return $this->morphOne(File::class, 'model')->latestOfMany();
    }

    public function avatarUrl()
    {
      return count($this->images)
        ? Storage::disk('avatars')->url($this->latestImage->path)
        : 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($this->email)));
    }

    public function profilePicture(): Attribute
    {

      return Attribute::make(
        fn () => count($this->images)
          ? Storage::disk('avatars')->url($this->latestImage->path)
          : 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($this->email)))
      );

    }

    protected static function boot()
    {
      parent::boot();

      static::created(function ($user) {

        if (static::count() === 1) {

          $user->role = 'admin';

          $user->save();

        }

      });

    }
}
