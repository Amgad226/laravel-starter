<?php

namespace App\Notification\Enums;

use App\Traits\BaseEnum;

enum OSEnum: string
{
    use BaseEnum;

    case ANDROID = 'ANDROID';
    case IOS = 'IOS';
}
