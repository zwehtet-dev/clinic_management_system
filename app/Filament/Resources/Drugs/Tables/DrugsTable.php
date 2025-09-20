<?php

namespace App\Filament\Resources\Drugs\Tables;

use Carbon\Carbon;
use App\Models\Drug;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Filters\Filter;
use Filament\Actions\BulkActionGroup;
use Filament\Schemas\Components\Grid;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;

class DrugsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),
                TextColumn::make('catelog')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),
                TextColumn::make('generic_name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('drugForm.name')
                    ->label('Form')
                    ->badge()
                    ->color('info'),
                TextColumn::make('strength')
                    ->searchable(),
                TextColumn::make('unit')
                    ->searchable(),
                TextColumn::make('total_stock')
                    ->sortable()
                    ->alignCenter()
                    ->color(function (Drug $record): string {
                        $stock = (int) $record->stock;
                        $minStock = $record->min_stock;

                        if ($stock == 0) return 'danger';
                        if ($stock <= $minStock) return 'warning';
                        return 'success';
                    })
                    ->weight(function (Drug $record): string {
                        $stock = (int) $record->stock;
                        return $stock <= $record->min_stock ? 'bold' : 'normal';
                    }),
                TextColumn::make('min_stock')
                    ->label('Min. Stock')
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('stock_alert')
                    ->label('Stock Alert')
                    ->getStateUsing(fn (Drug $record): bool => (int) $record->stock <= $record->min_stock)
                    ->boolean()
                    ->trueIcon('heroicon-o-exclamation-triangle')
                    ->falseIcon('heroicon-o-check-circle')
                    ->trueColor('danger')
                    ->falseColor('success')
                    ->alignCenter(),

                TextColumn::make('expire_alert')
                    ->label('Expire Alert')
                    ->numeric()
                    ->suffix(' Day/s')
                    ->toggleable(),

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
                SelectFilter::make('drug_form_id')
                    ->label('Drug Form')
                    ->relationship('drugForm', 'name')
                    ->searchable()
                    ->preload(),
                Filter::make('low_stock')
                    ->label('Low Stock Alert')
                    ->query(fn (Builder $query): Builder => $query->where('is_active', true)
                        ->whereHas('batches', function ($batchQuery) {
                            $batchQuery->where('quantity_available', '>', 0)
                                      ->where('expiry_date', '>', now());
                        })
                        ->whereRaw('(SELECT COALESCE(SUM(quantity_available), 0) FROM drug_batches WHERE drug_batches.drug_id = drugs.id AND quantity_available > 0 AND expiry_date > ?) <= min_stock', [now()])
                    ),
                Filter::make('out_of_stock')
                    ->label('Out of Stock')
                    ->query(fn (Builder $query): Builder => $query->whereDoesntHave('batches', function ($batchQuery) {
                        $batchQuery->where('quantity_available', '>', 0)
                                  ->where('expiry_date', '>', now());
                    })),
                TernaryFilter::make('is_active')
                    ->label('Status')
                    ->placeholder('All drugs')
                    ->trueLabel('Active drugs')
                    ->falseLabel('Inactive drugs'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('add_stock')
                    ->label('Add Stock')
                    ->icon('heroicon-o-plus')
                    ->color('success')
                    ->form([

                        Grid::make(2)->schema([

                            TextInput::make('batch_number')
                                ->label('Batch Number')
                                ->required(),

                            DatePicker::make('received_date')
                                ->label('Received Date')
                                ->default(now()),

                            TextInput::make('purchase_price')
                                ->label('Purchase Price / Unit')
                                ->numeric()
                                ->suffix(' Ks'),

                            TextInput::make('sell_price')
                                ->label('Sell Price / Unit')
                                ->numeric()
                                ->required()
                                ->suffix(' Ks'),

                            TextInput::make('quantity_received')
                                ->label('Quantity to Add')
                                ->required()
                                ->numeric()
                                ->minValue(1),

                            DatePicker::make('expiry_date')
                                ->label('Expiry Date')
                                ->required(),
                        ]),

                    ])->action(function (Drug $record, array $data): void {
                        // Create drug batch
                        $record->drugBatches()->create([
                            'batch_number' => $data['batch_number'],
                            'purchase_price' => $data['purchase_price'] ?? null,
                            'sell_price' => $data['sell_price'],
                            'quantity_received' => $data['quantity_received'],
                            'quantity_available' => $data['quantity_received'],
                            'expiry_date' => $data['expiry_date'],
                            'received_date' => $data['received_date'],
                        ]);
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
