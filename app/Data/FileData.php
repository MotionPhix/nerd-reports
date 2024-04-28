<?php

namespace App\Data;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

/** @typescript * */
class FileData extends Data
{
    public function __construct(
      public int|Optional $id,

      public string|null|Optional $fid,

      public string|Optional $filename,

      public string|Optional $mime_type,

      public string|Optional $full_url,

      public string|Optional $file_path,

      public int|Optional $user_id,

      public UserData|Optional $user,

      public int|Optional $size
    ) {}
}
