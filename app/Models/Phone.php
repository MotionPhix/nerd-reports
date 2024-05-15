<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Phone extends Model
{
  use HasFactory;

  protected $fillable = [
    'country_code',
    'is_primary_phone',
    'formatted',
    'number',
    'type',
  ];

  public function phoneable(): MorphTo
  {
    return $this->morphTo();
  }
}
