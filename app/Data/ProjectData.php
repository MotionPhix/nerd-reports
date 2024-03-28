<?php

namespace App\Data;

use Spatie\LaravelData\Data;

class ProjectData extends Data
{
    public function __construct(
        public readonly string $pid,

        public readonly string $name,

        public readonly string $due_date,

        public readonly Title|Optional $title,

        public readonly string|null|Optional $job_title,

        public readonly FirmData|null|Optional $firm,

        /** @var Collection<EmailData> */
        public Collection|Optional $emails,
    ) {}
}
