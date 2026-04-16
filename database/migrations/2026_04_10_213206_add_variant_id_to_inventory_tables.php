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
        Schema::table('warehouse_stocks', function (Blueprint $table) {
            $table->foreignId('variant_id')->nullable()->after('product_id')->constrained('product_variants')->cascadeOnDelete();
            $table->dropUnique(['product_id', 'warehouse_id']);
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->foreignId('variant_id')->nullable()->after('product_id')->constrained('product_variants')->cascadeOnDelete();
            $table->dropIndex(['product_id', 'warehouse_id']);
            $table->index(['product_id', 'variant_id', 'warehouse_id']);
        });

        Schema::table('purchase_items', function (Blueprint $table) {
            $table->foreignId('variant_id')->nullable()->after('product_id')->constrained('product_variants')->cascadeOnDelete();
        });

        Schema::table('transfer_items', function (Blueprint $table) {
            $table->foreignId('variant_id')->nullable()->after('product_id')->constrained('product_variants')->cascadeOnDelete();
        });

        Schema::table('product_locations', function (Blueprint $table) {
            $table->foreignId('variant_id')->nullable()->after('product_id')->constrained('product_variants')->cascadeOnDelete();
            $table->dropUnique(['warehouse_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_locations', function (Blueprint $table) {
            $table->unique(['warehouse_id', 'product_id']);
            $table->dropConstrainedForeignId('variant_id');
        });

        Schema::table('transfer_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('variant_id');
        });

        Schema::table('purchase_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('variant_id');
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropIndex(['product_id', 'variant_id', 'warehouse_id']);
            $table->index(['product_id', 'warehouse_id']);
            $table->dropConstrainedForeignId('variant_id');
        });

        Schema::table('warehouse_stocks', function (Blueprint $table) {
            $table->unique(['product_id', 'warehouse_id']);
            $table->dropConstrainedForeignId('variant_id');
        });
    }
};
