<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;

class File extends Model
{
  use HasFactory;

  protected $fillable = [
    'name',
    'path',
  ];

  public function fileable(): MorphTo
  {
    return $this->morphTo();
  }

  public function fullPath(): string
  {
    return Storage::disk('files')->path($this->path);
  }

  public function fileSize(): string
  {
    $sizeInBytes = Storage::size($this->path);
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];

    $index = 0;

    while ($sizeInBytes >= 1024 && $index < count($units) - 1) {
      $sizeInBytes /= 1024;
      $index++;
    }

    return round($sizeInBytes, 2) . ' ' . $units[$index];
  }
}
