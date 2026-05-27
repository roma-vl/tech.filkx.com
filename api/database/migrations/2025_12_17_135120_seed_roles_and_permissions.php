<?php

use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::transaction(function () {

            // 1. Detach permissions to avoid FK hell
            DB::table('permission_role')->delete();

            // 2. Delete only system permissions
            DB::table('permissions')
                ->where('is_system', true)
                ->delete();

            // 3. Delete only system roles
            DB::table('roles')
                ->where('is_system', true)
                ->delete();

            // 4. Run seeder
            Artisan::call('db:seed', [
                '--class' => RolesAndPermissionsSeeder::class,
                '--force' => true,
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
