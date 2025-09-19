<?php

namespace App\Filament\Resources\Expenses\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\DatePicker;

class ExpenseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Expense Information')
                    ->schema([
                        Select::make('expense_category_id')
                            ->label('Category')
                            ->relationship('expenseCategory', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->createOptionForm([
                                TextInput::make('name')->required(),
                                Textarea::make('description'),
                                Toggle::make('is_active')->default(true),
                            ]),

                        TextInput::make('amount')
                            ->required()
                            ->numeric()
                            ->suffix('Ks')
                            ->step(0.01)
                            ->minValue(0),

                        DatePicker::make('expense_date')
                            ->required()
                            ->default(now()),
                    ])
                    ->columns(2),

                Section::make('Additional Information')
                    ->schema([
                        Textarea::make('notes')
                            ->rows(4)
                            ->columnSpanFull()
                            ->placeholder('Expense details, receipt number, vendor information...'),
                    ])
                    ->collapsible(),
            ]);
    }
}
