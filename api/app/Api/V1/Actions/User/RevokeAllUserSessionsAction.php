<?php

namespace App\Api\V1\Actions\User;

use App\Models\User;

class RevokeAllUserSessionsAction
{
    public function execute(User $user): int
    {
        $currentToken = $user->token();
        $query = $user->tokens()->where('revoked', false);
        if ($currentToken) {
            $query->where('id', '!=', $currentToken->id);
        }
        return $query->update(['revoked' => true]);
    }
}
