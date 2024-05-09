<?php

namespace App\Classes;

class StoredFile
{
    public function __construct(
        public string $url,
        public string $path,
        public string $name,
        public string $folder_path,
    ) {
    }
}
