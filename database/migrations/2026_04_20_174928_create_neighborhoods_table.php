<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('neighborhoods', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('district_id');
            $table->tinyInteger('code')->unsigned();
            $table->string('name');
            $table->timestamps();

            $table->foreign('district_id')->references('id')->on('districts')->cascadeOnDelete();
            $table->unique(['district_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('neighborhoods');
    }
};
