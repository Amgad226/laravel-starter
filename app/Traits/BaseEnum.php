<?php

namespace App\Traits;

trait BaseEnum
{
    public static function getValuesToArray(): array
    {
        return  array_column(self::cases(), 'value');
    }

    public static function getNamesToArray(): array
    {
        return  array_column(self::cases(), 'name');
    }
    static function getNamesToString(): string
    {
        return implode('-', self::getNamesToArray());
    }

    static function getValuesToString(): string
    {
        return implode('-', self::getValuesToArray());
    }
    public static function isValidValue(string $value): bool
    {
        return in_array($value, self::getValuesToArray());
    }
    public static function getKeyByValue($value): string
    {
        foreach (self::cases() as $enum) {
            if ($enum->value == $value) {
                return $enum->name;
            }
        }
        abort(500,"Not found enum by value : $value");
        throw new \Exception("Not found enum by value : $value");
    }
    public static function getValueByName($name): string
    {
        foreach (self::cases() as $enum) {
            if ($enum->name == $name) {
                return $enum->value;
            }
        }
        abort(500,"Not found enum by value : $name");
    }
}
