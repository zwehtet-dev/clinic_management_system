<?php

namespace App\Filament\Widgets;

use App\Models\DailyReport;
use Filament\Widgets\ChartWidget;
use Carbon\Carbon;

class PatientVisitsChartWidget extends ChartWidget
{
    protected ?string $heading = 'Patient Activity (Last 30 Days)';

    protected static ?int $sort = 5;

    // protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        $data = DailyReport::where('report_date', '>=', Carbon::now()->subDays(30))
            ->orderBy('report_date')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Daily Visits',
                    'data' => $data->pluck('visits_count')->toArray(),
                    'borderColor' => '#8b5cf6',
                    'backgroundColor' => 'rgba(139, 92, 246, 0.1)',
                    'type' => 'bar',
                ],
                [
                    'label' => 'New Patients',
                    'data' => $data->pluck('new_patients')->toArray(),
                    'borderColor' => '#06b6d4',
                    'backgroundColor' => 'rgba(6, 182, 212, 0.1)',
                    'type' => 'bar',
                ],
            ],
            'labels' => $data->pluck('report_date')->map(fn($date) => $date->format('M d'))->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
