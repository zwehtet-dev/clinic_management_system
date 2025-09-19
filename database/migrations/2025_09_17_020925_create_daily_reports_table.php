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

        Schema::create('daily_reports', function (Blueprint $table) {
            $table->id();
            $table->date('report_date')->unique(); // one report per day

            // Patient statistics
            $table->unsignedInteger('new_patients')->default(0);
            $table->unsignedInteger('total_patients')->default(0);

            // Visit statistics
            $table->unsignedInteger('visits_count')->default(0);
            $table->decimal('visit_revenue', 12, 2)->default(0);

            // Drug sales
            $table->unsignedInteger('drug_sales_count')->default(0);
            $table->decimal('drug_sale_revenue', 12, 2)->default(0);


            // Revenue summary
            $table->decimal('total_revenue', 12, 2)->default(0);
            $table->decimal('doctor_referral_fees', 12, 2)->default(0);

            // Expenses
            $table->unsignedInteger('expense_count')->default(0);
            $table->decimal('total_expenses', 12, 2)->default(0);

            // Net income
            $table->decimal('net_income', 12, 2)->default(0);

            // Audit fields
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_reports');
    }
};
