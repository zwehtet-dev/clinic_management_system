<?php

namespace App\Filament\Widgets;

use App\Models\ExpenseCategory;
use App\Models\Expense;
use Filament\Widgets\ChartWidget;
use Carbon\Carbon;

class ExpenseCategoryChartWidget extends ChartWidget
{
    protected ?string $heading = 'Expenses by Category (This Month)';

    protected static ?int $sort = 6;

    protected function getData(): array
    {
        $categories = ExpenseCategory::withCount(['expenses' => function ($query) {
                $query->whereMonth('expense_date', Carbon::now()->month)
                      ->whereYear('expense_date', Carbon::now()->year);
            }])
            ->with(['expenses' => function ($query) {
                $query->whereMonth('expense_date', Carbon::now()->month)
                      ->whereYear('expense_date', Carbon::now()->year);
            }])
            ->get();

        $data = $categories->map(function ($category) {
            return [
                'category' => $category->name,
                'amount' => $category->expenses->sum('amount'),
            ];
        })->where('amount', '>', 0);

        return [
            'datasets' => [
                [
                    'data' => $data->pluck('amount')->toArray(),
                    'backgroundColor' => [
                        '#ef4444', '#f97316', '#eab308', '#22c55e',
                        '#06b6d4', '#3b82f6', '#8b5cf6', '#ec4899',
                        '#6b7280', '#84cc16'
                    ],
                ],
            ],
            'labels' => $data->pluck('category')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
