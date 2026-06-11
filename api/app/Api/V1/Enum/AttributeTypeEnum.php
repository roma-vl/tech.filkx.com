<?php

namespace App\Api\V1\Enum;

enum AttributeTypeEnum: string
{
    case TEXT = 'text';
    case SELECT = 'select';
    case BOOLEAN = 'boolean';
    case NUMBER = 'number';
    case COLOR = 'color';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
