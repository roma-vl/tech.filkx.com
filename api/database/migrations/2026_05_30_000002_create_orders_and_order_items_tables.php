<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            // Customer Info
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_phone');

            // Shipping Details
            $table->string('shipping_country')->nullable();
            $table->string('shipping_city')->nullable();
            $table->string('shipping_address');

            // Payment & Delivery Methods
            $table->string('delivery_method');
            $table->string('payment_method');

            // Status fields
            $table->string('payment_status')->default('pending'); // pending, processing, paid, failed, refunded
            $table->string('status')->default('pending_payment'); // pending_payment, paid, processing, packed, shipped, delivered, completed, cancelled, refunded

            // Totals
            $table->decimal('total_price', 12, 2);

            // Shipping Tracking
            $table->string('carrier')->nullable();
            $table->string('tracking_number')->nullable();

            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('variant_id')->nullable()->constrained('product_variants')->nullOnDelete();

            // Snapshot of product at the time of purchase
            $table->string('product_name');
            $table->string('sku');
            $table->decimal('price', 12, 2);
            $table->integer('quantity')->default(1);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
