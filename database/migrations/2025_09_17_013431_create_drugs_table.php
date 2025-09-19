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
        Schema::create('drugs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('catelog')->nullable();
            $table->string('generic_name')->nullable();
            $table->foreignId('drug_form_id')->constrained('drug_forms'); // tablet, syrup, injection, etc.
            $table->string('strength'); // 500mg, 10ml, etc.
            $table->string('unit'); // pieces, ml, etc.
            $table->integer('min_stock')->default(10);
            $table->integer('expire_alert')->default(30); // days / default 1 month
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drugs');
    }
};
