<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('districts', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->unsignedSmallInteger('canton_id');
            $table->tinyInteger('code')->unsigned();
            $table->string('name');
            $table->timestamps();

            $table->foreign('canton_id')->references('id')->on('cantons')->cascadeOnDelete();
            $table->unique(['canton_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('districts');
    }
};
