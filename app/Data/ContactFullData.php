<?php

namespace App\Data;

use App\Enums\Title;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Optional;


/**
 * @param Lazy|Collection<int, PhoneData> $phones
 * @param Lazy|Collection<int, EmailData> $emails
 */
class ContactFullData extends Data
{
    public function __construct(
        public ?int $id = null,
        public ?string $cid = null,
        public string $first_name = '',
        public string $last_name = '',
        public ?string $nickname = null,
        public ?string $middle_name = null,
        public ?string $job_title = null,
        public ?string $bio = null,
        public ?Title $title = null,
        public Lazy|Collection|Optional $phones,
        public Lazy|Collection|Optional $emails,
        public Lazy|FirmData|Optional $firm,
    ) {
    }
}
