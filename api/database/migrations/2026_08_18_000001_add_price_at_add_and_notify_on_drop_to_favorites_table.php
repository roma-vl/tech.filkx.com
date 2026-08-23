<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Guarded rather than a bare alter: Laravel's RefreshDatabase re-runs migrate:fresh
        // whenever it detects the test connection is no longer inside a transaction (see
        // Illuminate\Foundation\Testing\RefreshDatabase::beginDatabaseTransaction), which
        // under CI's more resource-constrained runners has been observed to fire mid-suite
        // on the SQLite :memory: connection - re-running every migration against a schema
        // that's still (partially) there and crashing hundreds of unrelated tests on this
        // one bare ALTER TABLE. Checking first makes a stray re-run a no-op instead.
        Schema::table('favorites', function (Blueprint $table) {
            if (! Schema::hasColumn('favorites', 'price_at_add')) {
                $table->decimal('price_at_add', 12, 2)->nullable()->after('product_id');
            }
            if (! Schema::hasColumn('favorites', 'notify_on_drop')) {
                $table->boolean('notify_on_drop')->default(true)->after('price_at_add');
            }
        });
    }

    public function down(): void
    {
        Schema::table('favorites', function (Blueprint $table) {
            $table->dropColumn(['price_at_add', 'notify_on_drop']);
        });
    }
};
