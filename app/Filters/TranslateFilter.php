<?php

namespace App\Filters;

use Spatie\QueryBuilder\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class TranslateFilter
 * A custom filter used by the spatie/query-builder Package
 * @link https://spatie.be/docs/laravel-query-builder/v5/introduction
 * To filter on Translations created with astrotomic/translatable package
 * @link https://github.com/Astrotomic/laravel-translatable
 * @package App\custom_filters
 */
class TranslateFilter implements Filter
{
    /**
     * @param Builder $query
     * @param $value
     * @param string $property
     */
    public function __invoke(Builder $query, $value, string $property)
    {
        if (is_array($value)) {
            $first =  array_shift(($value));
            if (array_key_exists('base', $first)) {
                if ($first['base'] == 'and') {
                    $query->whereTranslation($property, $first['value']);
                } else {
                    $query->orWhereTranslation($property, $first['value']);
                }
            } else {
                $query->whereTranslation($property, $first['value']);
            }

            foreach ($value as $key => $data) {
                $query->orWhereTranslation($property, $data['value']);
            }
        }
    }
}
