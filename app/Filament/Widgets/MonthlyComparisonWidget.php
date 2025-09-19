<?php

namespace App\Filament\Widgets;

use App\Models\DailyReport;
use Filament\Widgets\ChartWidget;
use Carbon\Carbon;

class MonthlyComparisonWidget extends ChartWidget
{
    protected ?string $heading = 'Monthly Performance Comparison';

    protected static ?int $sort = 7;

    protected function getData(): array
    {
        $months = [];
        $revenues = [];
        $expenses = [];
        $netIncomes = [];

        // Get last 12 months data
        for ($i = 11; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $monthData = DailyReport::whereMonth('report_date', $month->month)
                ->whereYear('report_date', $month->year)
                ->selectRaw('
                    SUM(total_revenue) as total_revenue,
                    SUM(total_expenses) as total_expenses,
                    SUM(net_income) as net_income
                ')
                ->first();

            $months[] = $month->format('M Y');
            $revenues[] = $monthData->total_revenue ?? 0;
            $expenses[] = $monthData->total_expenses ?? 0;
            $netIncomes[] = $monthData->net_income ?? 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Monthly Revenue',
                    'data' => $revenues,
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                ],
                [
                    'label' => 'Monthly Expenses',
                    'data' => $expenses,
                    'borderColor' => '#ef4444',
                    'backgroundColor' => 'rgba(239, 68, 68, 0.1)',
                ],
                [
                    'label' => 'Monthly Net Income',
                    'data' => $netIncomes,
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                ],
            ],
            'labels' => $months,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}

