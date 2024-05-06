<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Str;
use Stevebauman\Purify\Casts\PurifyHtmlOnGet;

class Task extends Model
{
  use HasFactory;

  const POSITION_GAP = 60000;

  const POSITION_MIN = 0.00002;

  protected $fillable = [
    'name',
    'description',
    'assigned_to',
    'assigned_by',
    'status',
    'board_id',
    'position',
    'priority'
  ];

  protected function casts(): array
  {
    return [
      'created_at' => 'date:j F, Y',
      'description' => PurifyHtmlOnGet::class,
      'name' => PurifyHtmlOnGet::class,
    ];
  }

  protected function createdAt(): Attribute
  {
    return Attribute::make(
      get: fn($value) => Carbon::parse($value)->diffForHumans(),
    );
  }

  public function board(): BelongsTo
  {
    return $this->belongsTo(Board::class);
  }

  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class, 'assigned_to');
  }

  public function assignee(): BelongsTo
  {
    return $this->belongsTo(User::class, 'assigned_by');
  }

  public function comments(): HasMany
  {
    return $this->hasMany(Comment::class);
  }

  public function files(): HasManyThrough
  {
    return $this->hasManyThrough(
      File::class,
      Comment::class,
      'task_id',
      'model_id',
      'id',
      'id'
    )->where('model_type', Comment::class);
  }

  public static function booted(): void
  {
    static::creating(function ($task) {

      $task->tid = Str::orderedUuid();

      $task->position = self::query()->where('board_id', $task->board_id)
          ->orderByDesc('position')
          ->first()?->position + self::POSITION_GAP;

    });

    static::updating(function ($task) {

      if (!isset($task->tid)) {

        $task->tid = Str::orderedUuid();

      }

    });

    /*static::saved(function ($task) {
      if ($task->position < self::POSITION_MIN) {
        \DB::statement("SET @previousPosition := 0");
        \DB::statement("
            UPDATE tasks
            SET position = (@previousPosition := @previousPosition + ?)
            WHERE board_id = ?
            ORDER BY position
          ", [
          self::POSITION_GAP,
          $task->board_id
        ]);
      }
    });*/

    static::saved(function ($task) {

      if ($task->position < self::POSITION_MIN) {

        $previousPosition = 0;

        $tasks = \DB::table('tasks')
          ->select('id')
          ->where('board_id', $task->board_id)
          ->orderBy('position')
          ->get();

        foreach ($tasks as $task) {
          \DB::table('tasks')
            ->where('id', $task->id)
            ->update(['position' => $previousPosition += self::POSITION_GAP]);
        }
      }
    });
  }
}
