<?php

namespace App\Data;

use App\Enums\AddressType;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/** @typescript **/
class AddressData extends Data
{
    public function __construct(
        public ?int $id,
        public AddressType $type,
        public string $street,
        public string $city,
        public ?string $state,
        public string $country,
    ) {}
}
