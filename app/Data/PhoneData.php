<?php

namespace App\Data;

use App\Enums\PhoneType;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

/** @typescript **/
class PhoneData extends Data
{
  public function __construct(

    public int|Optional $id,

    public string|Optional $country_code,

    public PhoneType|Optional $type,

    public bool $is_primary_phone,

    public string|Optional $formatted,

    public string $number,

  ) {}
}
