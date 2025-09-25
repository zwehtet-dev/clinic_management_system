<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'report_date',
        'new_patients',
        'total_patients',
        'visits_count',
        'visit_revenue',
        'drug_sales_count',
        'drug_sale_revenue',
        'total_revenue',
        'doctor_referral_fees',
        'expense_count',
        'total_expenses',
        'net_income'
    ];

    protected $casts = [
        'report_date' => 'date',
        'new_patients' => 'integer',
        'total_patients' => 'integer',
        'visits_count' => 'integer',
        'visit_revenue' => 'decimal:2',
        'drug_sales_count' => 'integer',
        'drug_sale_revenue' => 'decimal:2',
        'total_revenue' => 'decimal:2',
        'doctor_referral_fees' => 'decimal:2',
        'expense_count' => 'integer',
        'total_expenses' => 'decimal:2',
        'net_income' => 'decimal:2',
    ];

    public static function generateReport($date = null): self
    {
        $date = $date ? \Carbon\Carbon::parse($date) : now();
        $dateString = $date->toDateString();

        // New patients registered on this date
        $newPatients = Patient::whereDate('created_at', $dateString)->count();

        // Total active patients up to this date
        $totalPatients = Patient::where('is_active', true)
            ->whereDate('created_at', '<=', $dateString)
            ->count();

        // Visits on this date
        $visitsCount = Visit::whereDate('visit_date', $dateString)->count();

        // Visit revenue (consultation fees)
        $visitRevenue = Visit::whereDate('visit_date', $dateString)
            ->sum('consultation_fee');

        // Drug sales on this date
        $drugSalesCount = DrugSale::whereDate('sale_date', $dateString)->count();

        // Drug sale revenue
        $drugSaleRevenue = DrugSale::whereDate('sale_date', $dateString)
            ->invoice->sum('total_amount');

        // Total revenue from invoices
        $totalRevenue = Invoice::where('status', 'paid')
            ->whereDate('invoice_date', $dateString)
            ->sum('total_amount');

        // Doctor referral fees
        $doctorReferralFees = DoctorReferral::where('status', 'paid')
            ->whereHas('visit', function ($query) use ($dateString) {
                $query->whereDate('visit_date', $dateString);
            })
            ->sum('referral_fee');

        // Expenses on this date
        $expenseCount = Expense::whereDate('expense_date', $dateString)->count();
        $totalExpenses = Expense::whereDate('expense_date', $dateString)->sum('amount');

        // Net income
        $netIncome = $totalRevenue - $totalExpenses - $doctorReferralFees;

        // Create or update report
        return self::updateOrCreate(
            ['report_date' => $dateString],
            [
                'new_patients' => $newPatients,
                'total_patients' => $totalPatients,
                'visits_count' => $visitsCount,
                'visit_revenue' => $visitRevenue,
                'drug_sales_count' => $drugSalesCount,
                'drug_sale_revenue' => $drugSaleRevenue,
                'total_revenue' => $totalRevenue,
                'doctor_referral_fees' => $doctorReferralFees,
                'expense_count' => $expenseCount,
                'total_expenses' => $totalExpenses,
                'net_income' => $netIncome,
            ]
        );
    }

    public static function getMonthSummary($month = null, $year = null): array
    {
        $month = $month ?: now()->month;
        $year = $year ?: now()->year;

        $reports = self::whereMonth('report_date', $month)
            ->whereYear('report_date', $year)
            ->get();

        return [
            'total_days' => $reports->count(),
            'new_patients' => $reports->sum('new_patients'),
            'total_visits' => $reports->sum('visits_count'),
            'total_revenue' => $reports->sum('total_revenue'),
            'total_expenses' => $reports->sum('total_expenses'),
            'net_income' => $reports->sum('net_income'),
            'average_daily_revenue' => $reports->count() > 0 ? $reports->avg('total_revenue') : 0,
            'average_daily_visits' => $reports->count() > 0 ? $reports->avg('visits_count') : 0,
        ];
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('report_date', now()->month)
                    ->whereYear('report_date', now()->year);
    }

    public function scopeThisYear($query)
    {
        return $query->whereYear('report_date', now()->year);
    }

    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('report_date', [$startDate, $endDate]);
    }

}
