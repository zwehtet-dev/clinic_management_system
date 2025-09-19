<?php
namespace App\Filament\Widgets;

use App\Models\DailyReport;
use Filament\Widgets\ChartWidget;
use Carbon\Carbon;

class RevenueChartWidget extends ChartWidget
{
    protected ?string $heading = 'Revenue vs Expenses (Last 30 Days)';

    protected static ?int $sort = 4;

    // protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        $data = DailyReport::where('report_date', '>=', Carbon::now()->subDays(30))
            ->orderBy('report_date')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Revenue',
                    'data' => $data->pluck('total_revenue')->toArray(),
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'fill' => true,
                ],
                [
                    'label' => 'Expenses',
                    'data' => $data->pluck('total_expenses')->toArray(),
                    'borderColor' => '#ef4444',
                    'backgroundColor' => 'rgba(239, 68, 68, 0.1)',
                    'fill' => true,
                ],
                [
                    'label' => 'Net Income',
                    'data' => $data->pluck('net_income')->toArray(),
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'fill' => true,
                ],
            ],
            'labels' => $data->pluck('report_date')->map(fn($date) => $date->format('M d'))->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                ],
            ],
        ];
    }
}
