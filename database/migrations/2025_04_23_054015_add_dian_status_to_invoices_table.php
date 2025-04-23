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
        Schema::table('invoices', function (Blueprint $table) {
            $table->enum('dian_status', ['PENDING', 'SENT', 'ACCEPTED', 'REJECTED'])->default('PENDING')->after('notes');
            $table->string('dian_response_code')->nullable()->after('dian_status');
            $table->text('dian_response_message')->nullable()->after('dian_response_code');
            $table->integer('dian_retry_count')->default(0)->after('dian_response_message');
            $table->timestamp('dian_sent_at')->nullable()->after('dian_retry_count');
            $table->timestamp('dian_processed_at')->nullable()->after('dian_sent_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn([
                'dian_status', 
                'dian_response_code', 
                'dian_response_message', 
                'dian_retry_count',
                'dian_sent_at',
                'dian_processed_at'
            ]);
        });
    }
};
