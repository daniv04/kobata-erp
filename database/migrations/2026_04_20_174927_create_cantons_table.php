<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cantons', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->unsignedTinyInteger('province_id');
            $table->tinyInteger('code')->unsigned();
            $table->string('name');
            $table->timestamps();

            $table->foreign('province_id')->references('id')->on('provinces')->cascadeOnDelete();
            $table->unique(['province_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cantons');
    }
};
