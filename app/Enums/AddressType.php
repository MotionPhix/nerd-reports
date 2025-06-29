<?php

namespace App\Enums;

enum AddressType: string
{
  case Home = 'home';

  case Work = 'work';

  case Other = 'other';

  public function label(): string
  {
    return match ($this) {
      self::Home => 'Home',
      self::Work => 'Work',
      self::Other => 'Other',
    };
  }
}
