<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

  protected $fillable = [
    'body',
    'task_id',
    'user_id',
  ];

  public function task()
  {
    return $this->belongsTo(Task::class);
  }

  public function user()
  {
    return $this->belongsTo(User::class);
  }

  public function files()
  {
    return $this->morphMany(File::class, 'model');
  }
}
