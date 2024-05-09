<?php

namespace App\Enums;

use App\Traits\BaseEnum;

enum StoreInSessionKeyEnum: string
{
    use BaseEnum;
    case IMAGE = "image";
    case IMAGES = "images";
    case FILE = "file";
    case FILES = "files";
}
