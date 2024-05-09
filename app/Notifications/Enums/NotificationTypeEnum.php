<?php

namespace App\Notification\Enums;

use App\Traits\BaseEnum;

enum NotificationTypeEnum: string
{
    use BaseEnum;
    case NOTIFICATION = 'notification';
    case AUTHENTICATION = 'authentication';
}
