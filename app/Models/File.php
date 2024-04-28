<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class File extends Model
{
  use HasFactory;

  protected $guarded = [];

  public function model(): MorphTo
  {
    return $this->morphTo();
  }

  public function fullUrl(): Attribute
  {
    return Attribute::make(
      get: fn() => url('files/' . $this->user_id . '/' . $this->created_at->format('Y') . '/' . $this->created_at->format('m') . '/' . $this->filename),
    );
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

  protected static function boot()
  {
    parent::boot();

    static::creating(function ($file) {
      $file->fid = Str::orderedUuid();
    });

    static::updating(function ($file) {
      if (!isset($file->fid)) {
        $file->fid = Str::orderedUuid();
      }
    });
  }
}
