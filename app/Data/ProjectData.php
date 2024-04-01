<?php

namespace App\Data;

use DateTime;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

/** @typescript **/
class ProjectData extends Data
{
    public function __construct(
        public readonly string $pid,

        public readonly string $name,

        public readonly string $created_at,

        public readonly string $due_date,

        public readonly string $status,

        public readonly ContactData $contact,
    ) {}
}
