<?php

namespace App\Data;

use Spatie\LaravelData\Data;

class FirmData extends Data
{
    public function __construct(
        public ?int $id = null,
        public ?string $fid = null,
        public string $name = '',
        public ?string $slogan = null,
        public ?AddressData $address = null,
        public ?string $url = null
    ) {
    }
}
