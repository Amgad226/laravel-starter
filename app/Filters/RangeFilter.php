<?php


namespace App\Filters;

use Spatie\QueryBuilder\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class RangeFilter
 * A custom filter used by the spatie/query-builder Package
 * @link https://spatie.be/docs/laravel-query-builder/v5/introduction
 * To filter on ranges for numeric and time fields
 * @package App\custom_filters
 */
class RangeFilter implements Filter
{
    /**
     * @param Builder $query
     * @param $value
     * @param string $property
     */
    //TODO must improve this filter shape 
    public function __invoke(Builder $query, $value, $property)
    {
        if (is_array($value)) {
            $start =  array_shift(($value));
            $end =  array_shift(($value));

            if (array_key_exists('base', $start)) {
                if ($start['base'] == 'between' || $start['base'] == null) {
                    $query->whereBetween($property, [$start['value'], $end['value']]);
                } else {
                    $query->orWhereBetween($property, [$start['value'], $end['value']]);
                }
            } else {
                $query->whereBetween($property, [$start['value'], $end['value']]);
            }
        }
    }
}
