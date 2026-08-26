<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('favorites', function (Blueprint $table) {
            if (! Schema::hasColumn('favorites', 'notify_on_restock')) {
                $table->boolean('notify_on_restock')->default(false)->after('notify_on_drop');
            }
        });
    }

    public function down(): void
    {
        Schema::table('favorites', function (Blueprint $table) {
            $table->dropColumn('notify_on_restock');
        });
    }
};
