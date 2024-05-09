<?php

namespace App\Enums;

use App\Traits\BaseEnum;

enum LogEnum: string
{
    use BaseEnum; 

    case EDIT = "EDIT";
    case ADD = "ADD";
    case DELETE = "DELETE";
    case ACTIVITY = "ACTIVITY";
    case GET = "GET";
}
