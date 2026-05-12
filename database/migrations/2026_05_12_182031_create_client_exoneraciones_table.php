<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_exoneraciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->char('tipo_documento', 2);
            $table->string('tipo_documento_otro', 100)->nullable();
            $table->string('numero_documento', 40);
            $table->unsignedInteger('articulo')->nullable();
            $table->unsignedInteger('inciso')->nullable();
            $table->char('nombre_institucion', 2);
            $table->string('nombre_institucion_otros', 160)->nullable();
            $table->dateTime('fecha_emision');
            $table->decimal('tarifa_exonerada', 4, 2);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_exoneraciones');
    }
};
