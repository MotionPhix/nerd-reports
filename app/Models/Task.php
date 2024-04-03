<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
      get: fn ($value) => Carbon::parse($value)->diffForHumans(),
    );
  }

  public function board()
  {
    return $this->belongsTo(Board::class);
  }

  public function assignedTo()
  {
    return $this->belongsTo(User::class, 'assigned_to');
  }

  public static function booted()
  {
    static::creating(function ($task) {
      $task->position = self::query()->where('board_id', $task->board_id)->orderByDesc('position')->first()?->position + self::POSITION_GAP;
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
