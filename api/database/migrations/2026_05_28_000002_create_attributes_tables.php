<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attributes', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->json('name'); // Multi-language name e.g. {"uk": "Колір", "en": "Color"}
            $table->string('type')->default('text'); // text, select, boolean, number, color
            $table->timestamps();
        });

        Schema::create('attribute_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attribute_id')->constrained('attributes')->cascadeOnDelete();
            $table->json('value'); // Multi-language value or raw value e.g. {"uk": "Червоний"} or "#FF0000"
            $table->timestamps();
        });

        Schema::create('product_attribute_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('variant_id')->nullable()->constrained('product_variants')->cascadeOnDelete();
            $table->foreignId('attribute_id')->constrained('attributes')->cascadeOnDelete();
            $table->foreignId('attribute_value_id')->nullable()->constrained('attribute_values')->cascadeOnDelete();
            $table->text('custom_value')->nullable(); // For free-text values
            $table->timestamps();

            $table->unique(['product_id', 'variant_id', 'attribute_id'], 'prod_var_attr_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_attribute_values');
        Schema::dropIfExists('attribute_values');
        Schema::dropIfExists('attributes');
    }
};
