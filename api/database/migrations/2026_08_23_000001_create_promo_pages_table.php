<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promo_pages', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('badge')->nullable();
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->text('description')->nullable();
            $table->string('image_path');
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('promo_page_product', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promo_page_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->unique(['promo_page_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promo_page_product');
        Schema::dropIfExists('promo_pages');
    }
};
