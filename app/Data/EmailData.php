<?php

namespace App\Data;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

/** @typescript **/
class EmailData extends Data
{
    public function __construct(
        public int|Optional $id,
        public string $email,
        public bool $is_primary_email,
    ) {}
}
