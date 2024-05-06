<?php

namespace App\Data;

use App\Enums\AddressType;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/** @typescript **/
class AddressData extends Data
{
  public function __construct(
    public int|Optional $id,

    public AddressType $type,

    public string|Optional $state,

    public string|Optional $country,

    public string $street,

    public string $city
  ) {}
}
