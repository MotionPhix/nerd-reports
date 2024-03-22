<?php

namespace App\Data;

use App\Enums\PhoneType;
use Spatie\LaravelData\Data;

class PhoneData extends Data
{
    public function __construct(
        public ?int $id,
        public ?string $country_code,
        public string $number = '',
        public ?PhoneType $type,
        public bool $is_primary_phone = false,
        public ?string $formatted
    ) {}
}
