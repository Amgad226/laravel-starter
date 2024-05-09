<?php

namespace App\Enums;
use App\Traits\BaseEnum;

enum SuspensionStatusEnum:string 
{
    use BaseEnum;
    const PENDING = 'pending';
    const ACTIVE = 'active';
    const BLOCKED = 'blocked';
    const SUSPENDED = 'suspended';
    const REJECTED = 'rejected';

    public static function getTranslatedValue($value): string
    {
        return match ($value) {
            self::PENDING => ' بانتظار الموافقة',
            self::ACTIVE => 'تفعيل',
            self::BLOCKED => 'حظر',
            self::SUSPENDED => 'ايقاف',
            self::REJECTED => 'رفض',
            default => 'Unknown',
        };
    }

}
