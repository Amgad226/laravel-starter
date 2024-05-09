<?php

namespace App\Http\Requests;

use App\Enums\ModuleNameEnum;
use App\Traits\TranslateValidation;
use Illuminate\Foundation\Http\FormRequest;

class BaseRequest extends FormRequest
{
    use TranslateValidation;

    public function getTranslatedAttributes(array $attribute_keys, ModuleNameEnum $module_name, string $folder_name = '')
    {
        return $this->getValuesByKeys($attribute_keys, $module_name, $folder_name);
    }
}
