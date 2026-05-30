<?php

namespace App\Api\V1\Enum;

enum DiscountTypeEnum: string
{
    case PERCENT = 'percent';
    case FIXED = 'fixed';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
