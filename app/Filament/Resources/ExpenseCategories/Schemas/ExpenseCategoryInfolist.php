<?php

namespace App\Filament\Resources\ExpenseCategories\Schemas;

use Filament\Schemas\Schema;
use App\Models\ExpenseCategory;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;

class ExpenseCategoryInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Category Information')
                    ->schema([
                        TextEntry::make('name')
                            ->size('lg')
                            ->weight('bold'),

                        TextEntry::make('is_active')
                            ->badge()
                            ->color(fn (bool $state): string => $state ? 'success' : 'danger')
                            ->formatStateUsing(fn (bool $state): string => $state ? 'Active' : 'Inactive'),

                        TextEntry::make('expenses_count')
                            ->label('Total Expenses')
                            ->getStateUsing(fn (ExpenseCategory $record): int => $record->expenses()->count())
                            ->badge()
                            ->color('primary'),

                        TextEntry::make('total_amount')
                            ->label('Total Amount')
                            ->getStateUsing(fn (ExpenseCategory $record): string =>
                                '$' . number_format($record->expenses()->sum('amount'), 2)
                            )
                            ->badge()
                            ->color('success'),

                        TextEntry::make('created_at')
                            ->label('Created')
                            ->dateTime(),
                    ])
                    ->columns(2),

                Section::make('Description')
                    ->schema([
                        TextEntry::make('description')
                            ->markdown()
                            ->columnSpanFull(),
                    ])
                    ->visible(fn (ExpenseCategory $record): bool => !empty($record->description)),
                    ]);
    }
}
