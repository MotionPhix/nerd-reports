<?php

namespace App\Data;

use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

/** @typescript **/
class NotificationData extends Data
{
  public function __construct(

    public string|Optional $id,

    public string|Optional $type,

    public string|Optional $notifiable_type,

    public int|Optional $notifiable_id,

    /** @var Collection<UserData|CommentData|TaskData|ProjectData> */
    public Collection $data,

    public string|Optional $read_at,

    public string|Optional $created_at,

    public string|Optional $updated_at

  ) {

    $this->data = $data->mapWithKeys(function ($item) {

      return [$item->getKey() => $item];

    })->groupBy(function ($item) {

      return get_class($item);

    });
  }
}
