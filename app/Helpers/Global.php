<?php

use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Profile;


/**
 * Creates an API response object.
 *
 * @param string $message The message to include in the response.
 * @param mixed $data The data to include in the response, defaults to null.
 * @param int $status The HTTP status code for the response, defaults to 200.
 * @param array|null $meta Any other additional data to include in the response, defaults to null.
 *
 * @return 
 */
function apiResponse($message, $data = [], $status = 200, $meta = null, $extra = null)
{
    return response()->json(
        [
            'status' => $status,
            'message' => $message,
            'data' => $data,
            'meta' => $meta,
            'extra' => $extra,
        ],
        $status
    );
}

/**
 * Creates an API Error response object.
 *
 * @param string $message The message to include in the response.
 * @param mixed $errors The data to include in the response, defaults to null.
 * @param int $status The HTTP status code for the response

 * @param array|null $other Any other additional data to include in the response, defaults to null.
 *
 * @return 
 */
function apiErrorResponse($message, $status, $errors = [])
{
    return response()->json(
        [
            'status' => $status,
            'message' => $message,
            'errors' => $errors,
        ],
        $status
    );
}


function getLangFromHeader(): string
{
    return (request()->header('Accept-Language')) ? request()->header('Accept-Language') : 'en';
}

function transferDataWithPagination($data)
{
    return  [
        'items' => $data->items(),
        'pagination' => [
            'total' => $data->total(),
            'perPage' => $data->perPage(),
            'currentPage' => $data->currentPage(),
            'from' => $data->firstItem(),
            'to' => $data->lastItem(),
            'lastPage' => $data->lastPage(),
        ]
    ];
}

function transferData($data)
{
    return  [
        'items' => $data

    ];
}

/**
 * Gets the fully-qualified class name of a model based on its key.
 * NOTE This helper function build on HMVC for that we search in Modules , we can edit it to search just in app/Models
 *
 * @param string $key The key of the model.
 *
 * @return string The fully-qualified class name of the model.
 */
function getModelByKey($key): string
{
    $key = Str::snake($key);
    $name = Str::studly($key);
    // $modules = array_keys(Module::all());
    $modules = array_keys([
        'module_name_1' => true,
        'module_name_2' => true,
    ]);
    $model_class = false;
    foreach ($modules as $module_name) {
        $path = DIRECTORY_SEPARATOR . "Modules" . DIRECTORY_SEPARATOR . $module_name . DIRECTORY_SEPARATOR . "Entities";

        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(base_path() . $path));
        $models = [];
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $models[] = $file;
            }
        }
        foreach ($models as $model) {

            $modelPath = $model->getPathname();

            if (explode('.', $model->getFileName())[0] === $name) {
                $model_class = str_replace([base_path(), '.php'], '', $modelPath);
            }
        }
    }
    return $model_class;
}


/**
 * Encrypts plaintext using AES-128-CBC algorithm.
 *
 * @param string $plaintext The data to be encrypted.
 *
 * @return string The encrypted data.
 */
function aes_128_encrypt($plaintext)
{
    $key = config('app.key');
    $algorithm = 'AES-128-CBC';

    $iv = hex2bin("9023459e9ad233b16bd03b0b67dfc209");

    $data = openssl_encrypt($plaintext, $algorithm, $key, 0, $iv);
    return $data;
}

/**
 * Decrypts ciphertext using AES-128-CBC algorithm.
 *
 * @param string $ciphertext The encrypted data to be decrypted.
 *
 * @return string The decrypted data.
 */
function aes_128_decrypt($ciphertext)
{
    $key = config('app.key');
    $algorithm = 'AES-128-CBC';

    $iv = hex2bin("9023459e9ad233b16bd03b0b67dfc209");

    return openssl_decrypt($ciphertext, $algorithm, $key, 0, $iv);
}


/**
 * Gets the authenticated user's profile data from the request headers.
 *
 * @return Profile|null The authenticated user's profile instance, or null if not found.
 */
function authProfile(): Profile|null
{
    return unserialize(request()->header('auth-profile')) ?: null;
}

/**
 * Sets the authenticated user's profile data in the request headers.
 *
 * @param Profile $profile The authenticated user's profile instance.
 *
 * @return void
 */
function setAuthProfile(Profile|null $profile): void
{
    request()->headers->set('auth-profile', serialize($profile));
}

function abortIfItemExists($modelClass, $conditions, $errorMessage)
{
    if ($modelClass::where($conditions)->exists()) {
        abort(400, $errorMessage);
    }
}
/**
 * Replaces all spaces in a string with underscores.
 *
 * @param string $str The string to be processed.
 *
 * @return string The processed string with all spaces replaced with underscores.
 */
function replaceSpacesWithUnderscores($str)
{
    $str = str_replace(' ', '_', $str);
    return $str;
}

function removeSpaces(string $string): string
{
    return preg_replace('/\s+/', ' ', trim($string));
}

function convertFromHisToHi($time)
{
    return Carbon::createFromFormat('H:i:s', $time)->format('H:i');
}
function getImageIfExists($path, $default = null): string
{
    $defaultImage = "404.svg";
    if (empty($path) || is_null($path) || !Storage::disk('local')->exists("public/$path")) {
        $path = $default ?? $defaultImage;
    }
    return url("storage/$path");
}
function getImagesIfExists($paths): array
{
    if (is_null($paths) || empty($paths)) {
        return [];
    }
    return array_map(
        function ($path) {
            return getImageIfExists($path);
        },
        $paths
    );
}


function stringBetweenTwoString($str, $starting_word, $ending_word)
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
