<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Products (and their variants) are now soft-deleted rather than
     * hard-deleted: an order placed against a product keeps its
     * order_items.variant_id pointing at a real row instead of it going
     * null when the product is later removed from the catalog, so past
     * orders/accounting reports don't lose track of what was actually sold.
     *
     * The plain unique index on slug/sku is replaced with a partial one
     * scoped to non-deleted rows, so a discontinued product's slug (or a
     * discontinued variant's SKU) becomes reusable once it's soft-deleted,
     * matching how a hard delete used to free it up.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->softDeletes();
        });

        DB::statement('ALTER TABLE products DROP CONSTRAINT IF EXISTS products_slug_unique');
        DB::statement('CREATE UNIQUE INDEX products_slug_unique ON products (slug) WHERE deleted_at IS NULL');

        DB::statement('ALTER TABLE product_variants DROP CONSTRAINT IF EXISTS product_variants_sku_unique');
        DB::statement('CREATE UNIQUE INDEX product_variants_sku_unique ON product_variants (sku) WHERE deleted_at IS NULL');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS product_variants_sku_unique');
        DB::statement('ALTER TABLE product_variants ADD CONSTRAINT product_variants_sku_unique UNIQUE (sku)');

        DB::statement('DROP INDEX IF EXISTS products_slug_unique');
        DB::statement('ALTER TABLE products ADD CONSTRAINT products_slug_unique UNIQUE (slug)');

        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
