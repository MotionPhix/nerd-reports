<?php

namespace App\Data;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

/** @typescript * */
class FileData extends Data
{
    public function __construct(
      public int|Optional $id,
      public string|Optional $name,
      public string|Optional $path,
    ) {}
}
