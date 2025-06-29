<?php

namespace App\Enums;

enum PhoneType: string
{
  case Mobile = 'mobile';

  case Work = 'work';

  case Home = 'home';

  case Fax = 'fax';

  public function label(): string
  {
    return match ($this) {
      self::Mobile => 'Mobile',
      self::Work => 'Work',
      self::Home => 'Home',
      self::Fax => 'Fax',
    };
  }
}
