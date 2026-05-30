<?php

namespace App\Api\V1\Actions\User;

use App\Models\User;

class UpdateUserProfileAction
{
    public function execute(User $user, array $data): User
    {
        // Update user direct fields
        $user->name = $data['name'] ?? $user->name;
        $user->email = $data['email'] ?? $user->email;
        
        // Save phone, language, addresses, and cards inside settings json
        $settings = $user->settings ?? [];
        if (array_key_exists('phone', $data)) {
            $settings['phone'] = $data['phone'];
        }
        if (array_key_exists('language', $data)) {
            $settings['language'] = $data['language'];
        }
        if (array_key_exists('addresses', $data)) {
            $settings['addresses'] = $data['addresses'];
        }
        if (array_key_exists('cards', $data)) {
            $settings['cards'] = $data['cards'];
        }
        $user->settings = $settings;

        $user->save();

        return $user;
    }
}
