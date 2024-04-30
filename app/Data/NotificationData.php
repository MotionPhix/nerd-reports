<?php

namespace App\Data;

use Spatie\LaravelData\Data;

/** @typescript **/
class NotificationData extends Data
{
    public function __construct(

      public readonly string $id,

      public readonly string $type,

      public readonly string $created_at,

      public readonly string $due_date,

      public readonly string $status,

      public readonly ContactData $contact,

    ) {}
}
