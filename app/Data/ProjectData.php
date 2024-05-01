<?php

namespace App\Data;

use App\Enums\ProjectStatus;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\EnumCast;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

/** @typescript **/
class ProjectData extends Data
{
    public function __construct(

      public readonly string $pid,

      public readonly string $name,

      public readonly string|Optional $created_at,

      public readonly string $due_date,

      #[WithCast(EnumCast::class)]
      public readonly ProjectStatus $status,

      public readonly ContactData $contact,

    ) {}
}
