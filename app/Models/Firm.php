<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Spatie\Tags\HasTags;

class Firm extends Model
{
  use HasFactory, HasTags, HasUuid;

  protected $table = 'firms';

  protected $primaryKey = 'uuid';

  public $incrementing = false;

  protected $keyType = 'string';

  protected $fillable = [
    'name',
    'slogan',
    'url',
  ];

  public function address(): MorphOne
  {
    return $this->morphOne(Address::class, 'addressable');
  }

  public function contacts(): HasMany
  {
    return $this->hasMany(Contact::class, 'firm_id', 'uuid');
  }

  public function projects(): HasManyThrough
  {
    return $this->hasManyThrough(Project::class, Contact::class, 'firm_id', 'contact_id', 'uuid', 'uuid');
  }

  protected static function boot(): void
  {

    parent::boot();

    static::deleting(function ($firm) {

      $firm->load('contacts.projects.tasks', 'tags');

      $firm->contacts->each(function ($contact) {

        $contact->projects->each(function ($project) {

          $project->tasks()->delete();

          $project->delete();

        });

        $contact->delete();

      });

      $firm->tags()->delete();

    });

  }
}
