<?php

namespace App\Api\V1\Actions\User;

use App\Models\User;

class GetUserSessionsAction
{
    public function execute(User $user): array
    {
        return $user->tokens()
            ->where('revoked', false)
            ->get()
            ->map(fn ($token) => [
                'id' => $token->id,
                'client_id' => $token->client_id,
                'name' => $token->name,
                'expires_at' => $token->expires_at,
                'created_at' => $token->created_at,
            ])
            ->toArray();
    }
}
