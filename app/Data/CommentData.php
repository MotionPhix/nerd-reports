<?php

namespace App\Data;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

/** @typescript * */
class CommentData extends Data
{
    public function __construct(

      public int|Optional $id,

      public string $body,

      public int $task_id,

      public string|Optional $created_at,

      public int|Optional $user_id,

      public UserData|Optional $user,

      public TaskData|Optional $task,

      /** @var Collection<FileData> */
      public Collection|null|Optional $files,
    ) {
      if (!$created_at instanceof Optional) $this->created_at = Carbon::parse($this->created_at)->diffForHumans();
    }
}
