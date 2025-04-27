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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('invoice_number', 50);
            $table->enum('type', ['income', 'credit', 'debit']);
            $table->enum('document_type', ['invoice', 'ticket_pos']);
            $table->string('cufe', 96)->unique()->nullable();
            $table->string('customer_id', 20);
            $table->string('customer_name', 255);
            $table->string('customer_email')->nullable();
            $table->decimal('subtotal', 20, 2);
            $table->decimal('tax', 20, 2);
            $table->decimal('total', 20, 2);
            $table->text('xml_path')->nullable();
            $table->text('pdf_path')->nullable();
            $table->text('signed_xml_path')->nullable();
            $table->string('access_token', 100)->unique()->nullable();
            $table->timestamp('issued_at');
            $table->timestamp('due_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Índice único para company_id + invoice_number (prevenir duplicidad)
            $table->unique(['company_id', 'invoice_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
}; 