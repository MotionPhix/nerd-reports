<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Email extends Model
{
  use HasFactory;

  protected $fillable = [
    'is_primary_email',
    'email',
  ];

  public function emailable(): MorphTo
  {
    return $this->morphTo();
  }
}
