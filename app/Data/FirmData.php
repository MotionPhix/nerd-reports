<?php

namespace App\Data;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

/** @typescript **/
class FirmData extends Data
{
    public function __construct(
        public int|Optional $id,
        public string|Optional $fid,
        public string|Optional $slogan,
        public AddressData|Optional $address,
        public string|Optional $url,
        public string $name
    ) {
    }
}
