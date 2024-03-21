<?php

namespace App\Data;

use App\Enums\Title;
use Spatie\LaravelData\Data;

/**
 * @property \App\Data\EmailData[] $emails
 */
class ContactData extends Data
{
    public function __construct(
      public readonly string $cid,
      public readonly string $first_name,
      public readonly string $last_name,
      public readonly ?Title $title,
      public readonly ?string $job_title,
      public readonly ?FirmData $firm,
      public readonly ?array $emails
    ) {}
}
