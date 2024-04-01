<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Str;
use Spatie\Tags\HasTags;

class Firm extends Model
{
    use HasFactory, HasTags;

    protected $fillable = [
        'name',
        'slogan',
        'url',
    ];

    public function address(): MorphOne
    {
        return $this->morphOne(Address::class, 'addressable');
    }

    public function contacts()
    {
        return $this->hasMany(Contact::class);
    }

    protected static function boot()
    {

        parent::boot();

        static::creating(function ($firm) {
            $firm->fid = Str::orderedUuid();
        });

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
