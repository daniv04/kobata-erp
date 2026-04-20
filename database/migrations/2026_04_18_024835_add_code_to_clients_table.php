<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('code')->nullable()->unique()->after('id');
        });

        // Backfill existing clients
        DB::table('clients')->orderBy('id')->each(function ($client, $index) {
            DB::table('clients')
                ->where('id', $client->id)
                ->update(['code' => 'KOB-'.str_pad($index + 1, 4, '0', STR_PAD_LEFT)]);
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn('code');
        });
    }
};
