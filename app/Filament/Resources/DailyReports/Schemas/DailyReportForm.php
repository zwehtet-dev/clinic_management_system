<?php

namespace App\Filament\Resources\DailyReports\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Group;

class DailyReportForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Report Date')
                    ->schema([
                        DatePicker::make('report_date')
                            ->required()
                            ->unique(ignoreRecord: true),
                    ]),

                Section::make('Patient Statistics')
                    ->schema([
                        TextInput::make('new_patients')
                            ->label('New Patients')
                            ->numeric()
                            ->default(0),

                        TextInput::make('total_patients')
                            ->label('Total Active Patients')
                            ->numeric()
                            ->default(0),
                    ])
                    ->columns(2),

                Group::make([
                    Section::make('Visit Statistics')
                        ->schema([
                            TextInput::make('visits_count')
                                ->label('Number of Visits')
                                ->numeric()
                                ->default(0),

                            TextInput::make('visit_revenue')
                                ->label('Visit Revenue')
                                ->numeric()
                                ->suffix('Ks')
                                ->default(0),
                        ])
                        ->columns(2),

                    Section::make('Drug Sales')
                        ->schema([
                            TextInput::make('drug_sales_count')
                                ->label('Drug Sales Count')
                                ->numeric()
                                ->default(0),

                            TextInput::make('drug_sale_revenue')
                                ->label('Drug Sale Revenue')
                                ->numeric()
                                ->suffix('Ks')
                                ->default(0),
                        ])
                        ->columns(2),
                    ]),

                Section::make('Financial Summary')
                    ->schema([
                        TextInput::make('total_revenue')
                            ->label('Total Revenue')
                            ->numeric()
                            ->suffix('Ks')
                            ->default(0),

                        TextInput::make('doctor_referral_fees')
                            ->label('Doctor Referral Fees')
                            ->numeric()
                            ->suffix('Ks')
                            ->default(0),

                        TextInput::make('expense_count')
                            ->label('Number of Expenses')
                            ->numeric()
                            ->default(0),

                        TextInput::make('total_expenses')
                            ->label('Total Expenses')
                            ->numeric()
                            ->suffix('Ks')
                            ->default(0),

                        TextInput::make('net_income')
                            ->label('Net Income')
                            ->numeric()
                            ->suffix('Ks')
                            ->default(0),
                    ])
                    ->columns(2),
            ]);
    }
}
