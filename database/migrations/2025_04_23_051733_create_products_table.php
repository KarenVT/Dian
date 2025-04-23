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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('merchant_id')->constrained()->onDelete('cascade');
            $table->string('sku');
            $table->string('name');
            $table->decimal('price', 15, 2);
            $table->decimal('tax_rate', 5, 2);
            $table->string('dian_code')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // SKU debe ser único por merchant
            $table->unique(['merchant_id', 'sku']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
