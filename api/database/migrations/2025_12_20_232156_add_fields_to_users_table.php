<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table
                ->string('avatar_path')
                ->nullable()
                ->after('timezone');

            $table
                ->string('status')
                ->default('active')
                ->after('avatar_path');

            $table
                ->timestamp('last_seen_at')
                ->nullable()
                ->after('status');

            $table
                ->timestamp('onboarding_completed_at')
                ->nullable()
                ->after('last_seen_at');

            $table
                ->json('settings')
                ->nullable()
                ->after('onboarding_completed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'avatar_path',
                'status',
                'last_seen_at',
                'onboarding_completed_at',
                'settings',
            ]);
        });
    }
};
