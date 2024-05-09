<?php

namespace App\Actions;

/**
 * This action used to decode a JSON string and returns an array of associative arrays based on the contents of the JSON
 */
class JsonToArrayOfArrayAction
{
    /**
     * Decodes a JSON string and returns an array of associative arrays based on the contents of the JSON.
     *
     * @param string $json The JSON string to be decoded.
     *
     * @return array An array of associative arrays based on the contents of the JSON.
     */
    public static function execute($json)
    {
        $array = json_decode($json);
        $array = array_map(function ($item) {
            return (array)$item;
        }, $array);
        return $array;
    }
}
