<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Board extends Model
{
  use HasFactory, HasUuid;

  protected $table = 'boards';

  protected $primaryKey = 'uuid';

  public $incrementing = false;

  protected $keyType = 'string';

  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
  protected $guarded = [];

  protected $fillable = [
    'name',
    'project_id',
  ];

  public function project(): BelongsTo
  {
    return $this->belongsTo(Project::class);
  }

  public function tasks(): HasMany
  {
    return $this->hasMany(Task::class);
  }
}
