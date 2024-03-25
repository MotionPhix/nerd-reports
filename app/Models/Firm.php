<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Str;

class Firm extends Model
{
    use HasFactory;

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

    }
}
