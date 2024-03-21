<?php

namespace App\Data;

use Spatie\LaravelData\Data;

class FirmData extends Data
{
    public function __construct(
      public string $name,
      public ?string $slogan,
      public ?AddressData $address,
      public ?string $url
    ) {}
}
