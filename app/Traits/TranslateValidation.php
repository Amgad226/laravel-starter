<?php

namespace App\Traits;

// use App\Enums\ModuleNameEnum;

/**
 * TranslateValidation trait that used with translate validation messages request 
 */
enum ModuleNameEnum
{
}
trait TranslateValidation
{

    protected function getValuesByKeys(array $keys, ModuleNameEnum $module_name, string $folder_name): array
    {
        return array_intersect_key($this->get_translated_call($module_name->value, $folder_name), array_flip($keys));
    }


    protected static  function get_translated_call(string $module_name, string $folder_name): array
    {
        $translated_call = [
            'category' => [
                'common' => [
                    'en' => __('category::key.common.en'),
                    'ar' => __('category::key.common.ar'),
                    'en.text' => __('category::key.common.en_text'),
                    'ar.text' => __('category::key.common.ar_text'),
                ],
                'province' => [],
                'city' => [
                    'province_id' => __('category::key.city.province_id'),
                    'file' => __('category::key.city.file'),
                ],
            ],
        ];
        if ($folder_name == "") {
            return array_merge(
                $translated_call[$module_name]['common'],
                $translated_call[$module_name],
            );
        }
        return array_merge(
            $translated_call[$module_name]['common'],
            $translated_call[$module_name][$folder_name],
        );
    }
}
