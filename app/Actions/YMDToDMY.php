<?php

namespace App\Actions;

use Carbon\Carbon;

/**
 * Converts a date from the yyyy-mm-dd format to the dd-mm-yyyy format.
 */
class YMDToDMY
{
    /**
     * Converts a date from the yyyy-mm-dd format to the dd-mm-yyyy format.
     *
     * @param string $date The date to be converted in the yyyy-mm-dd format.
     *
     * @return string The converted date in the dd-mm-yyyy format.
     */
    public static function execute($date)
    {
        $date = Carbon::createFromFormat('Y-m-d',  $date)->format('d-m-Y');
        return $date;
    }
}
