<?php

namespace App\Filament\Resources\ExpenseCategories\Tables;

use Filament\Tables\Table;
use App\Models\ExpenseCategory;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;

class ExpenseCategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),
                TextColumn::make('description')
                    ->limit(50)
                    ->wrap(),
                TextColumn::make('expenses_count')
                    ->label('Total Expenses')
                    ->counts('expenses')
                    ->alignCenter()
                    ->badge()
                    ->color('primary'),
                TextColumn::make('total_amount')
                    ->label('Total Amount')
                    ->getStateUsing(fn (ExpenseCategory $record): string =>
                        number_format($record->expenses()->sum('amount'), 2) . ' Ks'
                    )
                    ->alignCenter()
                    ->badge()
                    ->color('success'),

                TextColumn::make('this_month_amount')
                    ->label('This Month')
                    ->getStateUsing(fn (ExpenseCategory $record): string =>
                        number_format($record->expenses()->whereMonth('expense_date', now()->month)->sum('amount'), 2) . ' Ks'
                    )
                    ->alignCenter()
                    ->badge()
                    ->color('info'),
                IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->alignCenter(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Status')
                    ->placeholder('All categories')
                    ->trueLabel('Active categories')
                    ->falseLabel('Inactive categories'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('name');
    }
}
