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
        Schema::create('merchants', function (Blueprint $table) {
            $table->id();
            $table->string('nit', 10)->unique();
            $table->string('business_name', 255);
            $table->string('email')->unique();
            $table->string('password');
            $table->enum('tax_regime', ['COMÚN', 'SIMPLE', 'NO_RESPONSABLE_IVA']);
            $table->string('certificate_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('merchants');
    }
}; 