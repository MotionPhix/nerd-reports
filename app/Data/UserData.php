<?php

namespace App\Data;

use Spatie\LaravelData\Data;

class UserData extends Data
{
    public function __construct(
      public string $first_name,
      public string $last_name,
      public ?string $email = null,
      public ?string $name = '',
    ) {}
}
