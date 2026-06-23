<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('favorites', function (Blueprint $table) {
            $table->decimal('price_at_add', 12, 2)->nullable()->after('product_id');
            $table->boolean('notify_on_drop')->default(true)->after('price_at_add');
        });
    }

    public function down(): void
    {
        Schema::table('favorites', function (Blueprint $table) {
            $table->dropColumn(['price_at_add', 'notify_on_drop']);
        });
    }
};
