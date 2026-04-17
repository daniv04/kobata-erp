<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('hacienda_name');
            $table->string('contact_name')->nullable();
            $table->enum('id_number_type', ['fisica', 'juridica', 'dimex', 'nite']);
            $table->string('id_number')->unique();
            $table->string('economic_activity_code')->nullable();
            $table->string('economic_activity_description')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('province')->nullable();
            $table->string('canton')->nullable();
            $table->string('district')->nullable();
            $table->string('neighborhood')->nullable();
            $table->text('address')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
