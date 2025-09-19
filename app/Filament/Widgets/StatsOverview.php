<?php

namespace App\Filament\Widgets;

use App\Models\Drug;
use App\Models\Visit;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Patient;
use Illuminate\Support\Carbon;
use Filament\Widgets\StatsOverviewWidget;
use App\Filament\Resources\Drugs\DrugResource;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends StatsOverviewWidget
{

    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        // Get current month data
        $currentMonth = Carbon::now()->startOfMonth();
        $previousMonth = Carbon::now()->subMonth()->startOfMonth();

        // Patients statistics
        $totalPatients = Patient::where('is_active', true)->count();
        $newPatientsThisMonth = Patient::where('created_at', '>=', $currentMonth)->count();
        $newPatientsLastMonth = Patient::whereBetween('created_at', [
            $previousMonth,
            $previousMonth->copy()->endOfMonth()
        ])->count();
        $patientGrowth = $newPatientsLastMonth > 0
            ? (($newPatientsThisMonth - $newPatientsLastMonth) / $newPatientsLastMonth) * 100
            : ($newPatientsThisMonth > 0 ? 100 : 0);

        // Visits statistics
        $totalVisitsThisMonth = Visit::where('visit_date', '>=', $currentMonth)->count();
        $totalVisitsLastMonth = Visit::whereBetween('visit_date', [
            $previousMonth,
            $previousMonth->copy()->endOfMonth()
        ])->count();
        $visitGrowth = $totalVisitsLastMonth > 0
            ? (($totalVisitsThisMonth - $totalVisitsLastMonth) / $totalVisitsLastMonth) * 100
            : ($totalVisitsThisMonth > 0 ? 100 : 0);

        // Today's visits
        $todayVisits = Visit::whereDate('visit_date', Carbon::today())->count();
        $pendingVisits = Visit::where('status', 'pending')->count();

        // Revenue statistics
        $revenueThisMonth = Invoice::where('status', 'paid')
            ->where('invoice_date', '>=', $currentMonth)
            ->sum('total_amount');
        $revenueLastMonth = Invoice::where('status', 'paid')
            ->whereBetween('invoice_date', [
                $previousMonth,
                $previousMonth->copy()->endOfMonth()
            ])
            ->sum('total_amount');
        $revenueGrowth = $revenueLastMonth > 0
            ? (($revenueThisMonth - $revenueLastMonth) / $revenueLastMonth) * 100
            : ($revenueThisMonth > 0 ? 100 : 0);

        // Expenses statistics
        $expensesThisMonth = Expense::where('expense_date', '>=', $currentMonth)->sum('amount');
        $expensesLastMonth = Expense::whereBetween('expense_date', [
            $previousMonth,
            $previousMonth->copy()->endOfMonth()
        ])->sum('amount');

        // Net profit
        $netProfitThisMonth = $revenueThisMonth - $expensesThisMonth;
        $netProfitLastMonth = $revenueLastMonth - $expensesLastMonth;
        $profitGrowth = $netProfitLastMonth != 0
            ? (($netProfitThisMonth - $netProfitLastMonth) / abs($netProfitLastMonth)) * 100
            : ($netProfitThisMonth > 0 ? 100 : ($netProfitThisMonth < 0 ? -100 : 0));



        // Stock alerts
        $allDrugs = Drug::where('is_active', true)->with('batches')->get();

        $lowStockCount = $allDrugs->filter(fn($drug) => $drug->total_stock <= $drug->min_stock)->count();

        $outOfStockCount = $allDrugs->filter(fn($drug) => $drug->total_stock <= 0)->count();


        return [
            Stat::make('Total Active Patients', $totalPatients)
                ->description($newPatientsThisMonth . ' new this month')
                ->descriptionIcon($patientGrowth >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($patientGrowth >= 0 ? 'success' : 'danger')
                ->chart([
                    $newPatientsLastMonth ?: 0,
                    $newPatientsThisMonth ?: 0,
                ]),

            Stat::make('Visits This Month', $totalVisitsThisMonth)
                ->description(sprintf(
                    '%s vs last month • %d today • %d pending',
                    $visitGrowth >= 0 ? '+' . number_format($visitGrowth, 1) . '%' : number_format($visitGrowth, 1) . '%',
                    $todayVisits,
                    $pendingVisits
                ))
                ->descriptionIcon($visitGrowth >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($visitGrowth >= 0 ? 'success' : 'danger')
                ->chart([
                    $totalVisitsLastMonth ?: 0,
                    $totalVisitsThisMonth ?: 0,
                ]),

            Stat::make('Revenue This Month',  number_format($revenueThisMonth, 2).' Ks')
                ->description(sprintf(
                    '%s vs last month',
                    $revenueGrowth >= 0 ? '+' . number_format($revenueGrowth, 1) . '%' : number_format($revenueGrowth, 1) . '%'
                ))
                ->descriptionIcon($revenueGrowth >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($revenueGrowth >= 0 ? 'success' : 'danger')
                ->chart([
                    $revenueLastMonth ?: 0,
                    $revenueThisMonth ?: 0,
                ]),

            Stat::make('Net Profit This Month', number_format($netProfitThisMonth, 2).' Ks')
                ->description(sprintf(
                    '%s vs last month • Expenses: %sKs',
                    $profitGrowth >= 0 ? '+' . number_format(abs($profitGrowth), 1) . '%' : number_format($profitGrowth, 1) . '%',
                    number_format($expensesThisMonth, 2)
                ))
                ->descriptionIcon($profitGrowth >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($netProfitThisMonth >= 0 ? 'success' : 'danger')
                ->chart([
                    $netProfitLastMonth ?: 0,
                    $netProfitThisMonth ?: 0,
                ]),

            Stat::make('Stock Alerts', $lowStockCount)
                ->description($outOfStockCount . ' out of stock')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($lowStockCount > 0 ? 'warning' : 'success')
                ->url(DrugResource::getUrl('index', ['tableFilters[low_stock][isActive]' => true]))
        ];
    }
}
