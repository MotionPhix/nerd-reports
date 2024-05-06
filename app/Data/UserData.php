<?php

namespace App\Data;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

/** @typescript **/
class UserData extends Data
{
  public function __construct(

    public int|Optional $id,

    public string $first_name,

    public string $last_name,

    public string|Optional $avatar_url,

    public string|Optional $email,

    public string|Optional $name,

  ) {}
}
