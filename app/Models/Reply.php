<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Reply extends Model
{
  use HasFactory;

  protected $guarded = [];

  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class);
  }

  public function comment(): BelongsTo
  {
    return $this->belongsTo(Comment::class);
  }

  public function files(): MorphMany
  {
    return $this->morphMany(File::class, 'model');
  }
}
