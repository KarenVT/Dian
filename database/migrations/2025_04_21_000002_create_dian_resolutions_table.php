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
        Schema::create('dian_resolutions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('resolution_number');
            $table->date('resolution_date');
            $table->string('prefix')->nullable();
            $table->integer('start_number');
            $table->integer('end_number');
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('technical_key_status', ['active', 'inactive'])->default('active');
            $table->string('technical_key')->nullable();
            $table->string('software_id')->nullable(); 
            $table->string('pin')->nullable();
            $table->boolean('is_active')->default(true);
            $table->enum('type', ['electronic', 'pos'])->default('electronic');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dian_resolutions');
    }
}; 