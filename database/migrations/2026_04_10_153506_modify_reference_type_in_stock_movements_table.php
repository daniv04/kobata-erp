<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        DB::statement('ALTER TABLE stock_movements DROP CONSTRAINT IF EXISTS stock_movements_reference_type_check');
        DB::statement("ALTER TABLE stock_movements ADD CONSTRAINT stock_movements_reference_type_check CHECK (reference_type IN ('invoice', 'transfer', 'consignment', 'adjustment', 'purchase'))");
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        DB::statement('ALTER TABLE stock_movements DROP CONSTRAINT IF EXISTS stock_movements_reference_type_check');
        DB::statement("ALTER TABLE stock_movements ADD CONSTRAINT stock_movements_reference_type_check CHECK (reference_type IN ('invoice', 'transfer', 'consignment', 'adjustment'))");
    }
};
