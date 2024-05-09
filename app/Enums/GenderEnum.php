<?php

namespace App\Enums;

use App\Traits\BaseEnum;

enum GenderEnum: string
{
    use BaseEnum;

    case Male = 'male';
    case Female = 'female';
    case Other = 'other';
}
