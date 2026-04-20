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
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(['province', 'canton', 'district', 'neighborhood']);

            $table->unsignedTinyInteger('province_id')->nullable()->after('address');
            $table->unsignedSmallInteger('canton_id')->nullable()->after('province_id');
            $table->unsignedSmallInteger('district_id')->nullable()->after('canton_id');
            $table->unsignedBigInteger('neighborhood_id')->nullable()->after('district_id');

            $table->foreign('province_id')->references('id')->on('provinces')->nullOnDelete();
            $table->foreign('canton_id')->references('id')->on('cantons')->nullOnDelete();
            $table->foreign('district_id')->references('id')->on('districts')->nullOnDelete();
            $table->foreign('neighborhood_id')->references('id')->on('neighborhoods')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropForeign(['province_id', 'canton_id', 'district_id', 'neighborhood_id']);
            $table->dropColumn(['province_id', 'canton_id', 'district_id', 'neighborhood_id']);

            $table->string('province')->nullable()->after('address');
            $table->string('canton')->nullable()->after('province');
            $table->string('district')->nullable()->after('canton');
            $table->string('neighborhood')->nullable()->after('district');
        });
    }
};
