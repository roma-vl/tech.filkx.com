<?php

namespace App\Api\V1\Enum;

enum SupportStatusEnum: string
{
    case NEW = 'new';
    case ACCEPTED = 'accepted';
    case DONE = 'done';
    case ARCHIVED = 'archived';
    case DELETED = 'deleted';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
