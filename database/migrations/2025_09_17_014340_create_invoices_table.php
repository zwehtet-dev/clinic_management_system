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
            $table->string('invoice_number')->unique();
            $table->morphs('invoiceable'); // invoiceable_id + invoiceable_type (type = [visit,drug_sale] )
            $table->date('invoice_date');
            $table->decimal('total_amount', 10, 2);
            $table->text('notes')->nullable();
            $table->enum('status', ['paid', 'cancelled'])->default('paid');
            $table->timestamps();
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
