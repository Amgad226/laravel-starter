<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class GarbageCollector extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'garbage:collect';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';


    /**
     * Execute the console command.
     *
     * @return int
     */

    public function handle()
    {
        $days = 1;

        //TODO - write all files and folders that the garbage collector will check it
        $paths = [
            'temp' => [
                '' => ['type' => 'directory'],
            ],
            'public' => [
                // 'prescriptions/files' => ['type' => 'file']
            ],
            'chunks' => [
                '' => ['type' => 'directory']
            ]
        ];

        $deletedPaths = [];

        foreach ($paths as $disk => $directories) {
            foreach ($directories as $directory => $options) {
                $type = $options['type'];

                if ($type === 'directory') {
                    $items = Storage::disk($disk)->allDirectories($directory);
                } else {
                    $items = Storage::disk($disk)->allFiles($directory);
                }

                $filteredItems = array_filter($items, function ($item) use ($days, $disk) {
                    $creationTime = Storage::disk($disk)->lastModified($item);
                    //NOTE -  Be Carefull of time
                    return Carbon::createFromTimestamp($creationTime)->diffInMilliseconds() > $days;
                });

                foreach ($filteredItems as $filteredItem) {
                    if ($type === 'directory') {
                        Storage::disk($disk)->deleteDirectory($filteredItem);
                    } else {
                        Storage::disk($disk)->delete($filteredItem);
                    }

                    $deletedPaths[] = $filteredItem;
                }
            }
        }

        return $deletedPaths;
    }
}
