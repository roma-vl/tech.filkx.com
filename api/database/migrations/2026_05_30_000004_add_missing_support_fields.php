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
        Schema::table('support_tickets', function (Blueprint $table) {
            if (!Schema::hasColumn('support_tickets', 'handled_by')) {
                $table->string('handled_by')->default('human')->after('status');
            }
            if (!Schema::hasColumn('support_tickets', 'tags')) {
                $table->json('tags')->nullable()->after('handled_by');
            }
        });

        Schema::table('support_messages', function (Blueprint $table) {
            if (!Schema::hasColumn('support_messages', 'is_internal')) {
                $table->boolean('is_internal')->default(false)->after('is_admin');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('support_tickets', function (Blueprint $table) {
            $table->dropColumn(['handled_by', 'tags']);
        });

        Schema::table('support_messages', function (Blueprint $table) {
            $table->dropColumn('is_internal');
        });
    }
};
