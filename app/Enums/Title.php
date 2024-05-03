<?php

namespace App\Enums;

enum Title: string
{
  case MR = 'mr';

  case MRS = 'mrs';

  case MS = 'ms';

  case SIR = 'sr';

  case PROFESSOR = 'prof';

  case DR = 'dr';

  /**
   * @return string
   */
  public function getLabel(): string
  {
    return match($this) {

      self::MR => 'Mr',

      self::MRS => 'Mrs',

      self::MS => 'Ms',

      self::SIR => 'Sir',

      self::PROFESSOR => 'Professor',

      self::DR => 'Dr',

    };
  }
}
