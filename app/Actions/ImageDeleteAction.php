<?php

namespace App\Actions;

use Illuminate\Support\Facades\Storage;

/**
 * Class ImageDeleteAction
 *
 * The ImageDeleteAction is responsible to delete an image form a directory
 */
class ImageDeleteAction
{
    public static function execute($folder, $sub_folder , $disk='public')
    {
        $path = $folder.'/'.$sub_folder;
        Storage::disk($disk)->deleteDirectory($path);
    }
}
