<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // Streaming
            ['resource' => 'stream', 'action' => 'start', 'scope' => 'contextual'],
            ['resource' => 'stream', 'action' => 'stop', 'scope' => 'contextual'],
            ['resource' => 'stream', 'action' => 'update', 'scope' => 'contextual'],
            ['resource' => 'stream', 'action' => 'view', 'scope' => 'contextual'],

            // Videos
            ['resource' => 'video', 'action' => 'create', 'scope' => 'contextual'],
            ['resource' => 'video', 'action' => 'update', 'scope' => 'contextual'],
            ['resource' => 'video', 'action' => 'delete', 'scope' => 'contextual'],
            ['resource' => 'video', 'action' => 'view', 'scope' => 'contextual'],

            // Playlists
            ['resource' => 'playlist', 'action' => 'delete', 'scope' => 'contextual'],
            ['resource' => 'playlist', 'action' => 'create', 'scope' => 'contextual'],
            ['resource' => 'playlist', 'action' => 'view', 'scope' => 'contextual'],
            ['resource' => 'playlist', 'action' => 'update', 'scope' => 'contextual'],
            ['resource' => 'playlist', 'action' => 'manage_items', 'scope' => 'contextual'],
            ['resource' => 'playlist', 'action' => 'stream', 'scope' => 'contextual'],

            // Billing
            ['resource' => 'billing', 'action' => 'view', 'scope' => 'contextual'],
            ['resource' => 'billing', 'action' => 'update', 'scope' => 'contextual'],

            // Admin
            ['resource' => 'admin', 'action' => 'users', 'scope' => 'global'],
            ['resource' => 'admin', 'action' => 'roles', 'scope' => 'global'],
            ['resource' => 'admin', 'action' => 'permissions', 'scope' => 'global'],
        ];

        foreach ($permissions as $perm) {
            Permission::updateOrCreate(
                ['slug' => "{$perm['resource']}.{$perm['action']}"],
                [
                    'name' => ucfirst($perm['action']).' '.ucfirst($perm['resource']),
                    'resource' => $perm['resource'],
                    'action' => $perm['action'],
                    'is_system' => true,
                ]
            );
        }

        $admin = Role::updateOrCreate(
            ['slug' => 'admin'],
            [
                'name' => 'Administrator',
                'description' => 'Full platform access',
                'scope' => 'global',
                'is_system' => true,
            ]
        );

        $support = Role::updateOrCreate(
            ['slug' => 'support'],
            [
                'name' => 'Support',
                'description' => 'Customer support access',
                'scope' => 'global',
                'is_system' => true,
            ]
        );

        $owner = Role::updateOrCreate(
            ['slug' => 'owner'],
            [
                'name' => 'Owner',
                'description' => 'Channel owner - full control',
                'scope' => 'contextual',
                'is_system' => true,
            ]
        );

        $moderator = Role::updateOrCreate(
            ['slug' => 'moderator'],
            [
                'name' => 'Moderator',
                'description' => 'Can manage streams and videos',
                'scope' => 'contextual',
                'is_system' => true,
            ]
        );

        $userRole = Role::updateOrCreate(
            ['slug' => 'user'],
            [
                'name' => 'User',
                'description' => 'Default registered user',
                'scope' => 'global',
                'is_system' => true,
            ]
        );

        $admin->permissions()->sync(Permission::all());

        $support->givePermissions([
            'stream.view',
            'video.view',
            'playlist.view',
            'billing.view',
        ]);

        $owner->givePermissions([
            'stream.start',
            'stream.stop',
            'stream.update',
            'stream.view',
            'video.create',
            'video.update',
            'video.delete',
            'video.view',
            'playlist.create',
            'playlist.delete',
            'playlist.view',
            'playlist.update',
            'playlist.manage_items',
            'playlist.stream',
            'billing.view',
            'billing.update',
        ]);

        $moderator->givePermissions([
            'stream.start',
            'stream.stop',
            'stream.update',
            'stream.view',
            'video.create',
            'video.update',
            'video.view',
            'playlist.view',
            'playlist.update',
            'playlist.manage_items',
            'playlist.stream',
        ]);

        $userRole->givePermissions([
            'stream.start',
            'stream.stop',
            'stream.update',
            'stream.view',
            'video.create',
            'video.update',
            'video.delete',
            'video.view',
            'playlist.create',
            'playlist.delete',
            'playlist.view',
            'playlist.update',
            'playlist.manage_items',
            'playlist.stream',
        ]);
    }
}
