<?php

namespace App\Data;

use App\Enums\Title;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

/**
 * @property \App\Data\EmailData[] $emails
 * @typescript
 */
class ContactData extends Data
{
    public function __construct(
      public readonly string $cid,

      public readonly string $first_name,

      public readonly string $last_name,

      public readonly Title|Optional $title,

      public readonly string|null|Optional $job_title,

      public readonly FirmData|null|Optional $firm,

      /** @var Collection<EmailData> */
      public Collection|Optional $emails,
    ) {}
}
