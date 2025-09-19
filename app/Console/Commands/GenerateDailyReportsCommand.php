<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\DailyReport;
use Carbon\Carbon;

class GenerateDailyReportsCommand extends Command
{
    protected $signature = 'reports:generate-daily
                           {--date= : Generate report for specific date (YYYY-MM-DD)}
                           {--from= : Generate reports from date (YYYY-MM-DD)}
                           {--to= : Generate reports to date (YYYY-MM-DD)}
                           {--yesterday : Generate report for yesterday}
                           {--current-month : Generate reports for current month}';

    protected $description = 'Generate daily reports for clinic operations';

    public function handle()
    {
        $this->info('Generating daily reports...');

        if ($this->option('date')) {
            $this->generateSingleReport($this->option('date'));
        } elseif ($this->option('yesterday')) {
            $this->generateSingleReport(Carbon::yesterday());
        } elseif ($this->option('current-month')) {
            $this->generateCurrentMonth();
        } elseif ($this->option('from') && $this->option('to')) {
            $this->generateDateRange($this->option('from'), $this->option('to'));
        } else {
            // Default: generate report for yesterday
            $this->generateSingleReport(Carbon::yesterday());
        }

        $this->info('Daily reports generation completed!');
    }

    private function generateSingleReport($date)
    {
        $date = Carbon::parse($date);
        $this->info("Generating report for {$date->format('Y-m-d')}...");

        $report = DailyReport::generateReport($date);

        $this->table([
            'Date', 'New Patients', 'Visits', 'Revenue', 'Expenses', 'Net Income'
        ], [
            [
                $report->report_date->format('Y-m-d'),
                $report->new_patients,
                $report->visits_count,
                number_format($report->total_revenue, 2) . ' Ks',
                number_format($report->total_expenses, 2). ' Ks',
                number_format($report->net_income, 2). ' Ks'
            ]
        ]);
    }

    private function generateCurrentMonth()
    {
        $start = Carbon::now()->startOfMonth();
        $end = Carbon::now()->endOfMonth();

        $this->info("Generating reports for current month ({$start->format('Y-m-d')} to {$end->format('Y-m-d')})...");

        DailyReport::generateReportsForRange($start, $end);

        $this->info("Generated reports for current month.");
    }

    private function generateDateRange($from, $to)
    {
        $start = Carbon::parse($from);
        $end = Carbon::parse($to);

        $this->info("Generating reports from {$start->format('Y-m-d')} to {$end->format('Y-m-d')}...");

        DailyReport::generateReportsForRange($start, $end);

        $this->info("Generated reports for date range.");
    }
}
