<?php

namespace App\Helpers;

use App\Classes\StoredFile;
use App\Enums\StoreInSessionKeyEnum;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Resources\StoredFileCloudResource;
use Carbon\Carbon;
use Illuminate\Support\Str;



/**
 * Class UtilHelper
 *  
 **/

class Util
{

    public static function imageStore($folder, $file, $file_name, $disk = 'temp')
    {
        $folder_path = uniqid($folder . '_', false);
        $path = Storage::disk($disk)->putFileAs($folder_path, $file, $file_name);
        $stored_file  = new StoredFile(
            Storage::disk($disk)->url($path),
            $path,
            $file_name,
            $folder_path,
        );

        return $stored_file;
    }

    public static function ImageDelete($folder, $sub_folder, $disk = 'public')
    {
        $path = $folder . '/' . $sub_folder;
        Storage::disk($disk)->deleteDirectory($path);
    }
    public static function fileMoveToStorage(array $files, string $public_destination, StoreInSessionKeyEnum $session_key_to_forget = null, ?string $old_image = null, bool $use_unique_id_in_folder = false): array
    {
        if ($session_key_to_forget) {
            session()->forget($session_key_to_forget->value);
        }

        // If there's an old image, delete it from the public disk
        if ($old_image) {
            // Get the path of the old image on the public disk
            $old_image_path = str_replace(Storage::disk('public')->url('/'), '', $old_image);
            // Get the parent directory path of the old image
            $parent_dir_path = dirname($old_image_path);

            // Delete the old image file from the public disk
            Storage::disk('public')->delete($old_image_path);

            // Delete the parent directory if it's empty
            if (Storage::disk('public')->directories($parent_dir_path) === [] && Storage::disk('public')->files($parent_dir_path) === []) {
                Storage::disk('public')->deleteDirectory($parent_dir_path);
            }
        }

        $new_files = [];
        // Loop through each uploaded file
        foreach ($files as $file) {
            $file = (array)$file;
            // Get the path of the temporary file on the temp disk
            $temp_file_path = Storage::disk('temp')->path($file['path']);
            if (!file_exists($temp_file_path)) {
                abort(400, "The file associated with the session could not be found. It may have been deleted or already used.");
            }
            // Create an UploadedFile object from the temp file
            $current_file = new UploadedFile($temp_file_path, $file['name']);

            // Determine whether to include the unique ID in the folder path or the file name
            if ($use_unique_id_in_folder) {
                // Generate a new unique folder path in the public disk with the format "{$public_destination}{$unique_id}/"
                $new_folder_path = $public_destination . uniqid($public_destination . '_', false) . '/';
                // Use the original file name as the new file name
                $new_file_name = $current_file->getClientOriginalName();
            } else {
                // Generate a new unique filename with the format "file_name_unique_id"
                $file_name = pathinfo($file['name'], PATHINFO_FILENAME);
                $file_extension = $current_file->getClientOriginalExtension();
                $new_file_name = $file_name . '_' . uniqid() . '.' . $file_extension;
                // Generate the folder path with the format "{$public_destination}{$new_file_name}"
                $new_folder_path = $public_destination;
            }

            // Move the file from the temp disk to the public disk with the new folder path and filename
            $path = Storage::disk('public')->putFileAs($new_folder_path, $current_file, $new_file_name);
            // Delete the temporary directory containing the uploaded file on the temp disk
            Storage::disk('temp')->deleteDirectory($file['folder_path']);

            // Create a new StoredFile object representing the moved file
            $stored_file  = new StoredFile(
                Storage::disk('public')->url($new_folder_path), // The URL of the new folder path on the public disk
                $path, // The path of the file on the public disk
                $file['name'], // The original filename of the uploaded file
                $new_folder_path, // The new folder path on the public disk where the file was moved to
            );


            // Add the StoredFile object to the array of new files
            $new_files[] = $stored_file;
        }

        // Return the array of new files
        return $new_files;
    }

    public static function YMDToDMY($date)
    {
        $date = Carbon::createFromFormat('Y-m-d',  $date)->format('d-m-Y');
        return $date;
    }



    //!SECTION [File]


    public static function fileStore($folder, $file, $file_name, $disk = 'temp')
    {
        $folder_path = uniqid($folder . '_', false);
        $path = Storage::disk($disk)->putFileAs($folder_path, $file, replaceSpacesWithUnderscores($file_name));

        $stored_file  = new StoredFile(
            Storage::disk($disk)->url($path),
            $path,
            $file_name,
            $folder_path,
        );

        return $stored_file;
    }


    public static function fileStoreCloud($folder, $file, $file_name, $disk = 'google')
    {

        $path = Storage::disk($disk)->putFileAs($folder, $file, replaceSpacesWithUnderscores($file_name));

        $stored_file  = new StoredFile(
            Storage::disk($disk)->url($path),
            $path,
            $file_name,
            $folder
        );

        return $stored_file;
    }


    public static function fileDeleteCloud($path, $disk = 'google')
    {
        $path = Storage::disk($disk)->Delete($path);
    }

    public static function getGoogleDriveId($url)
    {
        $start_pos = strpos($url, "id=");
        $end_pos = strpos($url, "&");

        return substr($url, $start_pos + 3, $end_pos - $start_pos + strlen($url));
    }

    public static function stringBetweenTwoString($str, $starting_word, $ending_word)
    {
        $subtring_start = strpos($str, $starting_word);
        //Adding the starting index of the starting word to
        //its length would give its ending index
        $subtring_start += strlen($starting_word);
        //Length of our required sub string
        $size = strpos($str, $ending_word, $subtring_start) - $subtring_start;
        // Return the substring from the index substring_start of length size
        return substr($str, $subtring_start, $size);
    }

    // SECTION [Get data from session] 

    /**
     * Retrieves an image stored in the session and removes it from the session.
     *
     * @param bool $required Flag indicating whether an image is required to be in the session. If set to true and no image is found, an exception is thrown (optional, default is true).
     *
     * @return string|null The JSON-encoded image retrieved from the session, or null if no image was found and $required is false.
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException If $required is true and no image was found in the session.
     */
    public static function imageGetFromSession($required = true)
    {
        $image = session()->get('image');
        if ($image) {
            return json_encode($image);
        } else {
            if ($required) {
                abort(440, __("session.image")); //"session doesn't have image");
            }
            return null;
        }
    }


    /**
     * Retrieves any images stored in the session and removes them from the session.
     *
     * @param bool $required Flag indicating whether images are required to be in the session. If set to true and no images are found, an exception is thrown (optional, default is true).
     *
     * @return array|null The array of images retrieved from the session, or null if no images were found and $required is false.
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException If $required is true and no images were found in the session.
     */
    public static function imagesGetFromSession($required = true)
    {
        $images = session()->get('images');
        if ($images) {
            return $images;
        } else {
            if ($required) {
                abort(440, __("session.images")); //"session doesn't have images");
            }
            return null;
        }
    }

    public static function fileGetFromSession($required = true)
    {
        $prescription_file = session()->get('file');
        if ($prescription_file) {
            return json_encode($prescription_file);
        } else {
            if ($required) {
                abort(440, __("session.file")); //"session doesn't have file");
            }
            return null;
        }
    }

    public static function filesGetFromSession($required = true)
    {
        $prescription_file = session()->get('files');
        if ($prescription_file != null) {
            return json_encode($prescription_file);
        } else {
            if ($required) {
                abort(440, __("session.files")); //"session doesn't have files");
            }
            return null;
        }
    }


    public static function getVideoName($file): string
    {
        $extension = $file->extension();
        $file_name = str_replace('.' . $extension, '', $file->hashName());
        $file_name = str_replace(' ', '_', $file_name);
        $file_name = '_' . md5(time()) . '_' . $file_name . '.' . $extension;

        return $file_name;
    }
}
