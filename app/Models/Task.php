<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Stevebauman\Purify\Casts\PurifyHtmlOnGet;

class Task extends Model
{
  use HasFactory;

  const STATUSES = [
    'new' => 'New',
    'in_progress' => 'In Progress',
    'cancelled' => 'Cancelled',
    'done' => 'Completed'
  ];

  const POSITION_GAP = 60000;

  const POSITION_MIN = 0.00002;

  protected function casts(): array
  {
    return [
      'due_date' => 'date:j F, Y',
      'description' => PurifyHtmlOnGet::class,
    ];
  }

  public function board()
  {
    return $this->belongsTo(Board::class);
  }

  public function assignedTo()
  {
    return $this->belongsTo(User::class, 'assigned_to');
  }

  public function getStatusColorAttribute()
  {
    return [
      'done' => 'green',
      'cancelled' => 'red',
      'in_progress' => 'blue'
    ][$this->status] ?? 'gray';
  }

  public function getStatusDisplayAttribute()
  {
    return [
      'done' => 'Completed',
      'cancelled' => 'Cancelled',
      'in_progress' => 'In Progress'
    ][$this->status] ?? 'New';
  }

  public static function booted()
  {
    static::creating(function ($task) {
      $task->position = self::query()->where('board_id', $task->board_id)->orderByDesc('position')->first()?->position + self::POSITION_GAP;
    });

    static::saved(function ($task) {
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
    });
  }
}
