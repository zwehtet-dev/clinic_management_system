<?php

namespace App\Filament\Resources\Expenses\Schemas;

use App\Models\Expense;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;

class ExpenseInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Expense Information')
                    ->schema([
                        TextEntry::make('name')
                            ->label('Name')
                            ->weight('bold'),

                        TextEntry::make('expenseCategory.name')
                            ->label('Category')
                            ->badge()
                            ->color('info'),

                        TextEntry::make('amount')
                            ->numeric()
                            ->size('lg')
                            ->weight('bold')
                            ->color('danger')
                            ->suffix(' Ks'),

                        TextEntry::make('expense_date')
                            ->date(),

                        TextEntry::make('created_at')
                            ->label('Created')
                            ->dateTime(),
                    ])
                    ->columns(3),

                Section::make('Notes')
                    ->schema([
                        TextEntry::make('notes')
                            ->markdown()
                            ->columnSpanFull(),
                    ])
                    ->visible(fn (Expense $record): bool => !empty($record->notes)),

            ]);
    }
}
