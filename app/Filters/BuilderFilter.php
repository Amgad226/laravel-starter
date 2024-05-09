<?php

namespace App\Filters;

use Spatie\QueryBuilder\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class BuilderFilter
 * A custom filter used by the spatie/query-builder Package
 * @link https://spatie.be/docs/laravel-query-builder/v5/introduction
 * @link https://github.com/Astrotomic/laravel-translatable
 * @package App\custom_filters
 */
class BuilderFilter implements Filter
{
    /**
     * @param Builder $query
     * @param $value
     * @param string $property
     */
    public function __invoke(Builder $query, $value, string $property)
    {
        if (is_array($value)) 
        {
            $first =  array_shift(($value));
            if (array_key_exists('base', $first)) 
            {
                if ($first['base'] == 'and' || $first['base'] == null) 
                {
                    $query->where($property, $first['op'], $first['value']);
                } 
                else
                {
                    $query->orWhere($property, $first['op'], $first['value']);
                }
            }
            
            else 
            {
                $query->where($property, $first['op'], $first['value']);
            }
            foreach ($value as $key => $data) {
                $query->orWhere($property, $data['op'], $data['value']);
            }
        }
    }
}
