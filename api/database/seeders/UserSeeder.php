<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::where('slug', 'admin')->firstOrFail();
        $userRole = Role::where('slug', 'user')->firstOrFail();

        /*
        |--------------------------------------------------------------------------
        | Admin user
        |--------------------------------------------------------------------------
        */
        $admin = User::updateOrCreate(
            ['email' => 'admin@filkx.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        $admin->roles()->sync([$adminRole->id]);

        /*
        |--------------------------------------------------------------------------
        | Default user
        |--------------------------------------------------------------------------
        */
        $user = User::updateOrCreate(
            ['email' => 'user@filkx.com'],
            [
                'name' => 'User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        $user->roles()->sync([$userRole->id]);
    }
}
