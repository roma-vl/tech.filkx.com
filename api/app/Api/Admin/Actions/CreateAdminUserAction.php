<?php

namespace App\Api\Admin\Actions;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CreateAdminUserAction
{
    public function execute(array $data, string $ip, string $userAgent): User
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'status' => 'active',
        ]);

        event(new \App\Events\AuditEvent(new \App\Api\V1\Dto\AuditLogDto(
            action: 'user.created',
            domain: 'team',
            message: "Admin created new user: {$user->name} ({$user->email})",
            userId: \Illuminate\Support\Facades\Auth::id(),
            subjectType: User::class,
            subjectId: $user->id,
            payload: ['user_id' => $user->id, 'email' => $user->email],
            ipAddress: $ip,
            userAgent: $userAgent
        )));

        return $user;
    }
}
