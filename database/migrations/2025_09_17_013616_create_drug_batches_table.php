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
        Schema::create('drug_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('drug_id')->constrained()->onDelete('cascade');
            $table->string('batch_number');
            $table->decimal('purchase_price', 10, 2)->nullable();
            $table->decimal('sell_price', 10, 2);
            $table->integer('quantity_received')->default(0);
            $table->integer('quantity_available')->default(0);
            $table->date('expiry_date');
            $table->date('received_date')->nullable();
            $table->timestamps();
            $table->index(['drug_id', 'expiry_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drug_batches');
    }
};
