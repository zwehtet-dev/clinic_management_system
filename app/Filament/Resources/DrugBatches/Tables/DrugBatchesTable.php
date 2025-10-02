<?php

namespace App\Filament\Resources\DrugBatches\Tables;

use App\Models\DrugBatch;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Filters\Filter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Builder;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;

class DrugBatchesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('drug.public_id')
                    ->label('Drug ID')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('drug.name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('batch_number')
                    ->copyable()
                    ->searchable(),
                TextColumn::make('purchase_price')
                    ->numeric()
                    ->suffix(' Ks')
                    ->sortable(),
                TextColumn::make('sell_price')
                    ->numeric()
                    ->suffix(' Ks')
                    ->sortable(),
                TextColumn::make('quantity_received')
                    ->numeric()
                    ->alignCenter()
                    ->sortable(),
                TextColumn::make('quantity_available')
                    ->numeric()
                    ->alignCenter()
                    ->color(function (DrugBatch $record): string {
                        $stock = (int) $record->quantity_available;
                        $minStock = $record->drug->min_stock;

                        if ($stock == 0) return 'danger';
                        if ($stock <= $minStock) return 'warning';
                        return 'success';
                    })
                    ->sortable(),
                TextColumn::make('expiry_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('received_date')
                    ->date()
                    ->sortable(),
                IconColumn::make('expire_alert')
                    ->label('Expire Alert')
                    ->getStateUsing(fn (DrugBatch $record): bool => $record->is_expire_alert)
                    ->boolean()
                    ->trueIcon('heroicon-o-exclamation-triangle')
                    ->falseIcon('heroicon-o-check-circle')
                    ->trueColor('danger')
                    ->falseColor('success')
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
                Filter::make('out_of_stock')
                    ->label('Out of Stock')
                    ->query(fn (Builder $query): Builder => $query->where('quantity_available', '0')),
                // Filter::make('expire_alert')
                //     ->label('Expire Alert')
                //     ->query(fn (Builder $query): Builder => ),
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
