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
            $table->text('two_factor_secret')->nullable()->after('password')
                ->comment('Encrypted TOTP secret; set as soon as the user starts enrollment, before confirmation');
            $table->text('two_factor_recovery_codes')->nullable()->after('two_factor_secret')
                ->comment('Encrypted JSON array of one-time recovery codes');
            $table->timestamp('two_factor_confirmed_at')->nullable()->after('two_factor_recovery_codes')
                ->comment('Null until the user confirms enrollment with a valid code; 2FA is only enforced once set');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['two_factor_secret', 'two_factor_recovery_codes', 'two_factor_confirmed_at']);
        });
    }
};
