<?php

namespace App\Api\V1\Enum;

enum MarketingStatusEnum: string
{
    case ACTIVE = 'active';
    case EXPIRED = 'expired';
    case INACTIVE = 'inactive';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
