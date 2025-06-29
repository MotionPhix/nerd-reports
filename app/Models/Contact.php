<?php

namespace App\Models;

use App\Data\FirmData;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Spatie\Tags\HasTags;
use Stevebauman\Purify\Casts\PurifyHtmlOnGet;

class Contact extends Model
{
  use HasFactory, SoftDeletes, HasTags, HasUuid;

  protected $table = 'contacts';

  protected $primaryKey = 'uuid';

  public $incrementing = false;

  protected $keyType = 'string';

  protected $fillable = [
    'first_name',
    'last_name',
    'bio',
    'job_title',
    'title',
    'middle_name',
    'firm_id',
    'nickname'
  ];

  protected function casts(): array
  {
    return [
      'created_at' => 'date:d m, Y',
      'deleted_at' => 'date:d M, Y',
      'last_interaction' => 'datetime',
      'firm' => FirmData::class,
      'bio' => PurifyHtmlOnGet::class,
    ];
  }

  public function phones(): MorphMany
  {
    return $this->morphMany(Phone::class, 'phoneable');
  }

  public function emails(): MorphMany
  {
    return $this->morphMany(Email::class, 'emailable');
  }

  public function interactions(): HasMany
  {
    return $this->hasMany(Interaction::class, 'contact_id', 'uuid');
  }

  public function firm()
  {
    return $this->belongsTo(Firm::class, 'firm_id', 'uuid');
  }

  public function projects(): HasMany
  {
    return $this->hasMany(Project::class);
  }

  protected function fullName(): Attribute
  {
    return Attribute::make(
      get: fn() => "{$this->first_name} {$this->last_name}"
    );
  }

  public function primaryEmail(): Attribute
  {
    return Attribute::make(
      get: fn() => $this->emails->firstWhere('is_primary_email', true)?->email ?? null
    );
  }

  protected static function boot()
  {
    parent::boot();

    static::forceDeleting(function ($contact) {
      $contact->load('phones', 'emails', 'tags');

      $contact->phones()->delete();

      $contact->emails()->delete();
    });
  }
}
