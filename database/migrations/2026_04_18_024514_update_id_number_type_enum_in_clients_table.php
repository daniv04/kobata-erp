<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE clients DROP CONSTRAINT IF EXISTS clients_id_number_type_check');
        DB::statement("ALTER TABLE clients ADD CONSTRAINT clients_id_number_type_check CHECK (id_number_type IN ('01','02','03','04'))");
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE clients DROP CONSTRAINT IF EXISTS clients_id_number_type_check');
        DB::statement("ALTER TABLE clients ADD CONSTRAINT clients_id_number_type_check CHECK (id_number_type IN ('fisica','juridica','dimex','nite'))");
    }
};
