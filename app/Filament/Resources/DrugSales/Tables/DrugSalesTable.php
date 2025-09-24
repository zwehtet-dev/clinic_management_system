<?php

namespace App\Filament\Resources\DrugSales\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DrugSalesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('public_id')
                    ->searchable(),
                // TextColumn::make('patient_id')
                //     ->numeric()
                //     ->sortable(),
                TextColumn::make('buyer_display_name')
                    ->label('Buyer')
                    ->searchable(),
                TextColumn::make('sale_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('total_amount')
                    ->numeric()
                    ->sortable(),
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
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
