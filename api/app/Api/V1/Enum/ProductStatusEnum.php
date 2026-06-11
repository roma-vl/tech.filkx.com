<?php

namespace App\Api\V1\Enum;

enum ProductStatusEnum: string
{
    case ACTIVE = 'active';
    case DRAFT = 'draft';
    case HIDDEN = 'hidden';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
