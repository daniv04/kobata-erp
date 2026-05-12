<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('
                CREATE UNIQUE INDEX IF NOT EXISTS warehouse_stocks_product_variant_warehouse_unique
                ON warehouse_stocks (product_id, variant_id, warehouse_id)
            ');

            return;
        }

        // Consolidate duplicate rows: keep the lowest id per group,
        // sum quantities and reserved_quantities, delete the rest.
        DB::statement('
            WITH grouped AS (
                SELECT
                    product_id,
                    variant_id,
                    warehouse_id,
                    MIN(id) AS keep_id,
                    SUM(quantity) AS total_quantity,
                    SUM(reserved_quantity) AS total_reserved
                FROM warehouse_stocks
                GROUP BY product_id, variant_id, warehouse_id
                HAVING COUNT(*) > 1
            )
            UPDATE warehouse_stocks ws
            SET quantity = g.total_quantity,
                reserved_quantity = g.total_reserved,
                updated_at = NOW()
            FROM grouped g
            WHERE ws.id = g.keep_id
        ');

        DB::statement('
            DELETE FROM warehouse_stocks ws
            USING warehouse_stocks ws2
            WHERE ws.product_id = ws2.product_id
              AND ws.warehouse_id = ws2.warehouse_id
              AND ws.variant_id IS NOT DISTINCT FROM ws2.variant_id
              AND ws.id > ws2.id
        ');

        // NULLS NOT DISTINCT ensures (product_id, NULL, warehouse_id) is also unique
        DB::statement('
            CREATE UNIQUE INDEX warehouse_stocks_product_variant_warehouse_unique
            ON warehouse_stocks (product_id, variant_id, warehouse_id)
            NULLS NOT DISTINCT
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('warehouse_stocks', function (Blueprint $table) {
            $table->dropIndex('warehouse_stocks_product_variant_warehouse_unique');
        });
    }
};
