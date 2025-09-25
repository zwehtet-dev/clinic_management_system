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
use App\Models\Doctor;
use Filament\Schemas\Components\Group;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends StatsOverviewWidget
{

    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        // Get current month data
        $currentMonth = Carbon::now()->startOfMonth();
        $previousMonth = Carbon::now()->subMonth()->startOfMonth();
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();

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

        // Doctors statistics
        $totalDoctors = Doctor::where('is_active', true)->count();
        $newDoctorsThisMonth = Doctor::where('created_at', '>=', $currentMonth)->count();
        $newDoctorsLastMonth = Doctor::whereBetween('created_at', [
            $previousMonth,
            $previousMonth->copy()->endOfMonth()
        ])->count();
        $doctorGrowth = $newDoctorsLastMonth > 0
            ? (($newDoctorsThisMonth - $newDoctorsLastMonth) / $newDoctorsLastMonth) * 100
            : ($newDoctorsThisMonth > 0 ? 100 : 0);

        // Drugs statistics
        $totalDrugs = Drug::where('is_active', true)->count();
        $newDrugsThisMonth = Drug::where('created_at', '>=', $currentMonth)->count();
        $newDrugsLastMonth = Drug::whereBetween('created_at', [
            $previousMonth,
            $previousMonth->copy()->endOfMonth()
        ])->count();
        $drugGrowth = $newDrugsLastMonth > 0
            ? (($newDrugsThisMonth - $newDrugsLastMonth) / $newDrugsLastMonth) * 100
            : ($newDrugsThisMonth > 0 ? 100 : 0);

        // Today`s new Patients statistics
        $totalNewPatientsToday = Patient::whereDate('created_at', $today)->count();
        $newPatientsYesterday = Patient::whereDate('created_at', $yesterday)->count();
        $patientGrowthToday = $newPatientsYesterday > 0
            ? (($totalNewPatientsToday - $newPatientsYesterday) / $newPatientsYesterday) * 100
            : ($totalNewPatientsToday > 0 ? 100 : 0);

        // Today's revenue
        $revenueToday = Invoice::where('status', 'completed')->sum('total_amount');

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
        $todayVisits = Visit::whereDate('visit_date', $today)->count();
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



        // Stock alerts - optimized query
        $lowStockCount = Drug::where('is_active', true)
            ->whereHas('batches', function ($query) {
                $query->where('quantity_available', '>', 0)
                      ->where('expiry_date', '>', now());
            })
            ->whereRaw('(SELECT COALESCE(SUM(quantity_available), 0) FROM drug_batches WHERE drug_batches.drug_id = drugs.id AND quantity_available > 0 AND expiry_date > ?) <= min_stock', [now()])
            ->count();

        $outOfStockCount = Drug::where('is_active', true)
            ->whereDoesntHave('batches', function ($query) {
                $query->where('quantity_available', '>', 0)
                      ->where('expiry_date', '>', now());
            })
            ->count();


        return [


            Group::make()
                ->schema([
                    Stat::make('Total Active Patients', $totalPatients)
                        ->description($newPatientsThisMonth . ' new this month')
                        ->descriptionIcon($patientGrowth >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                        ->color($patientGrowth >= 0 ? 'success' : 'danger')
                        ->chart([
                            $newPatientsLastMonth ?: 0,
                            $newPatientsThisMonth ?: 0,
                        ]),

                    Stat::make('Total Active Doctors', $totalDoctors)
                        ->description($newDoctorsThisMonth . ' new this month')
                        ->descriptionIcon($drugGrowth >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                        ->color($doctorGrowth >= 0 ? 'success' : 'danger')
                        ->chart([
                            $newDoctorsLastMonth ?: 0,
                            $newDoctorsThisMonth ?: 0,
                        ]),

                    Stat::make('Total Active Drugs', $totalDrugs)
                        ->description($newDrugsThisMonth . ' new this month')
                        ->descriptionIcon($doctorGrowth >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                        ->color($doctorGrowth >= 0 ? 'success' : 'danger')
                        ->chart([
                            $newDrugsLastMonth ?: 0,
                            $newDrugsThisMonth ?: 0,
                        ]),

                    Stat::make('Stock Alerts', $lowStockCount)
                        ->description($outOfStockCount . ' out of stock')
                        ->descriptionIcon('heroicon-m-exclamation-triangle')
                        ->color($lowStockCount > 0 ? 'warning' : 'success')
                        ->url(DrugResource::getUrl('index', ['tableFilters[low_stock][isActive]' => true])),
                ])
                ->columns(4)
                ->columnSpanFull(),

            // Group::make()
            //     ->schema([
            //         Stat::make('New Patients Today', $totalNewPatientsToday)
            //             ->description($newPatientsYesterday . ' new yesterday')
            //             ->descriptionIcon($patientGrowthToday >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
            //             ->color($patientGrowthToday >= 0 ? 'success' : 'danger')
            //             ->chart([
            //                 $newPatientsYesterday ?: 0,
            //                 $totalNewPatientsToday ?: 0,
            //             ]),




            //     ])
            //     ->columns(4)
            //     ->columnSpanFull(),



            Group::make()
                ->schema([
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
                ])
                ->columns(3)
                ->columnSpanFull()


        ];
    }
}
