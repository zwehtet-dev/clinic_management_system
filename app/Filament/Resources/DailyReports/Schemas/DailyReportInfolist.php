<?php

namespace App\Filament\Resources\DailyReports\Schemas;

use App\Models\DailyReport;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;

class DailyReportInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Daily Report Summary')
                    ->schema([
                        TextEntry::make('report_date')
                            ->date()
                            ->size('lg')
                            ->weight('bold'),

                        TextEntry::make('net_income')
                            ->suffix(' Ks')
                            ->size('lg')
                            ->weight('bold')
                            ->color(fn (DailyReport $record): string =>
                                $record->net_income >= 0 ? 'success' : 'danger'
                            ),
                    ])
                    ->columns(2),

                Section::make('Patient Statistics')
                    ->schema([
                        TextEntry::make('new_patients')
                            ->badge()
                            ->color('info'),

                        TextEntry::make('total_patients')
                            ->badge()
                            ->color('primary'),
                    ])
                    ->columns(2),

                Section::make('Visit & Sales Statistics')
                    ->schema([
                        TextEntry::make('visits_count')
                            ->label('Visits')
                            ->badge()
                            ->color('primary'),

                        TextEntry::make('visit_revenue')
                            ->suffix(' Ks')
                            ->color('success'),

                        TextEntry::make('drug_sales_count')
                            ->label('Drug Sales')
                            ->badge()
                            ->color('warning'),

                        TextEntry::make('drug_sale_revenue')
                            ->suffix(' Ks')
                            ->color('success'),
                    ])
                    ->columns(4),

                Section::make('Financial Breakdown')
                    ->schema([
                        TextEntry::make('total_revenue')
                            ->suffix(' Ks')
                            ->color('success'),

                        TextEntry::make('doctor_referral_fees')
                            ->suffix(' Ks')
                            ->color('warning'),

                        TextEntry::make('total_expenses')
                            ->suffix(' Ks')
                            ->color('danger'),

                        TextEntry::make('expense_count')
                            ->badge()
                            ->color('gray'),
                    ])
                    ->columns(4),

            ]);
    }
}
