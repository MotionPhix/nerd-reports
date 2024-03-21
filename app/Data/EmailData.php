<?php

namespace App\Data;

use Spatie\LaravelData\Data;

class EmailData extends Data
{
    public function __construct(
        public ?int $id,
        public string $email,
        public bool $is_primary_email = false,
    ) {}
}
