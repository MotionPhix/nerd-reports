<?php

namespace App\Data;

use App\Enums\Title;
use Spatie\LaravelData\Data;

/**
 * @property \App\Data\EmailData[] $emails
 * @property \App\Data\PhoneData[] $phones
 */
class ContactFullData extends Data
{
    public function __construct(
      public ?int $id,
      public ?string $cid,
      public string $first_name = '',
      public string $last_name = '',
      public ?string $nickname,
      public ?string $middle_name,
      public ?string $job_title,
      public ?string $bio,
      public ?Title $title,
      public ?array $phones,
      public ?array $emails,
      public ?FirmData $firm,
    ) {}
}
