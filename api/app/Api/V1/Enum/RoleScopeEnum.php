<?php

namespace App\Api\V1\Enum;

enum RoleScopeEnum: string
{
    case GLOBAL = 'global';
    case CONTEXTUAL = 'contextual';
}
