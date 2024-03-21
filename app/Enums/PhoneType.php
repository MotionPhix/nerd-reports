<?php

namespace App\Enums;

enum PhoneType: string
{
    case Mobile = 'mobile';

    case Work = 'work';

    case Home = 'home';

    case Fax = 'fax';
}
