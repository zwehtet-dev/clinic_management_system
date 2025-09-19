<?php

namespace App\Filament\Resources\DailyReports\Widgets;

use Carbon\Carbon;
use App\Models\DailyReport;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DailyReportStatsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        // Today's report
        $today = DailyReport::where('report_date', Carbon::today())->first();
        $yesterday = DailyReport::where('report_date', Carbon::yesterday())->first();

        // If today's report doesn't exist, generate it
        if (!$today) {
            $today = DailyReport::generateReport(Carbon::today());
        }

        // Calculate changes from yesterday
        $visitChange = $yesterday ?
            (($today->visits_count - $yesterday->visits_count) / max($yesterday->visits_count, 1)) * 100 : 0;
        $revenueChange = $yesterday ?
            (($today->total_revenue - $yesterday->total_revenue) / max($yesterday->total_revenue, 1)) * 100 : 0;
        $expenseChange = $yesterday ?
            (($today->total_expenses - $yesterday->total_expenses) / max($yesterday->total_expenses, 1)) * 100 : 0;

        // Get weekly data for charts
        $weeklyData = DailyReport::where('report_date', '>=', Carbon::now()->subDays(7))
            ->orderBy('report_date')
            ->get();

        return [
            Stat::make('Today\'s Visits', $today->visits_count)
                ->description(sprintf(
                    '%s%s vs yesterday',
                    $visitChange >= 0 ? '+' : '',
                    number_format($visitChange, 1) . '%'
                ))
                ->descriptionIcon($visitChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($visitChange >= 0 ? 'success' : 'danger')
                ->chart($weeklyData->pluck('visits_count')->toArray()),

            Stat::make('Today\'s Revenue', '$' . number_format($today->total_revenue, 2))
                ->description(sprintf(
                    '%s%s vs yesterday',
                    $revenueChange >= 0 ? '+' : '',
                    number_format($revenueChange, 1) . '%'
                ))
                ->descriptionIcon($revenueChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($revenueChange >= 0 ? 'success' : 'danger')
                ->chart($weeklyData->pluck('total_revenue')->toArray()),

            Stat::make('Today\'s Expenses', '$' . number_format($today->total_expenses, 2))
                ->description(sprintf(
                    '%s%s vs yesterday',
                    $expenseChange >= 0 ? '+' : '',
                    number_format($expenseChange, 1) . '%'
                ))
                ->descriptionIcon($expenseChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($expenseChange >= 0 ? 'danger' : 'success') // Inverse color for expenses
                ->chart($weeklyData->pluck('total_expenses')->toArray()),

            Stat::make('Today\'s Net Income', '$' . number_format($today->net_income, 2))
                ->description($today->net_income >= 0 ? 'Profitable Day' : 'Loss Day')
                ->descriptionIcon($today->net_income >= 0 ? 'heroicon-m-check-circle' : 'heroicon-m-x-circle')
                ->color($today->net_income >= 0 ? 'success' : 'danger')
                ->chart($weeklyData->pluck('net_income')->toArray()),

            Stat::make('New Patients Today', $today->new_patients)
                ->description('Registered today')
                ->descriptionIcon('heroicon-m-user-plus')
                ->color('info')
                ->chart($weeklyData->pluck('new_patients')->toArray()),
        ];
    }
}
