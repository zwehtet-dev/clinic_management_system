<?php

namespace App\Filament\Widgets;

use App\Models\Drug;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Widgets\TableWidget;
use Illuminate\Support\Facades\DB;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;

class StockAlertWidget extends TableWidget
{
    protected static ?string $heading = 'Stock Alerts';

    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';


    public function table(Table $table): Table
    {

        return $table
            ->query(fn (): Builder => Drug::query()
                ->where('is_active', true)
                ->where(function ($query) {
                    $query->whereRaw('(SELECT SUM(quantity_available)
                                        FROM drug_batches
                                        WHERE drug_batches.drug_id = drugs.id
                                        AND quantity_available > 0
                                        AND expiry_date > NOW()
                                    ) <= min_stock');
                })
                ->withSum(['batches as total_stock' => function ($query) {
                    $query->where('quantity_available', '>', 0)
                        ->where('expiry_date', '>', now());
                }], 'quantity_available')
                ->orderBy('total_stock', 'asc')
            )
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                TextColumn::make('drugForm.name')
                    ->label('Form')
                    ->badge()
                    ->color('info'),

                TextColumn::make('strength_unit')
                    ->label('Strength')
                    ->getStateUsing(fn (Drug $record): string => $record->strength . ' ' . $record->unit),

                TextColumn::make('stock')
                    ->sortable()
                    ->alignCenter()
                    ->color(function (Drug $record): string {
                        $stock = (int) $record->stock;
                        if ($stock == 0) return 'danger';
                        return 'warning';
                    })
                    ->weight('bold')
                    ->badge(),

                TextColumn::make('min_stock')
                    ->label('Min. Stock')
                    ->alignCenter()
                    ->badge()
                    ->color('gray'),

                TextColumn::make('alert_level')
                    ->label('Alert Level')
                    ->getStateUsing(function (Drug $record): string {
                        $stock = (int) $record->stock;
                        if ($stock == 0) return 'Out of Stock';
                        if ($stock <= $record->min_stock) return 'Low Stock';
                        return 'Normal';
                    })
                    ->badge()
                    ->color(function (Drug $record): string {
                        $stock = (int) $record->stock;
                        if ($stock == 0) return 'danger';
                        return 'warning';
                    }),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                //
            ])
            ->recordActions([
                Action::make('add_stock')
                    ->label('Add Stock')
                    ->icon('heroicon-o-plus')
                    ->color('success')
                    ->size('sm')
                    ->form([
                        TextInput::make('quantity')
                            ->label('Quantity to Add')
                            ->required()
                            ->numeric()
                            ->minValue(1),
                        TextInput::make('batch_number')
                            ->label('Batch Number')
                            ->required(),
                        TextInput::make('purchase_price')
                            ->label('Purchase Price per Unit')
                            ->numeric()
                            ->prefix('$'),
                        DatePicker::make('expiry_date')
                            ->label('Expiry Date')
                            ->required(),
                    ])
                    ->action(function (Drug $record, array $data): void {
                        // Create drug batch
                        $record->drugBatches()->create([
                            'batch_number' => $data['batch_number'],
                            'purchase_price' => $data['purchase_price'] ?? null,
                            'quantity' => $data['quantity'],
                            'expiry_date' => $data['expiry_date'],
                        ]);

                        // Update stock
                        $record->increment('stock', $data['quantity']);

                        // Send notification
                        Notification::make()
                            ->title('Stock Updated')
                            ->success()
                            ->body("Added {$data['quantity']} units to {$record->name}")
                            ->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    //
                ]),
            ])
            ->emptyStateHeading('No Stock Alerts')
            ->emptyStateDescription('All drugs are adequately stocked.')
            ->emptyStateIcon('heroicon-o-check-circle')
            ->striped()
            ->paginated([5, 10, 25]);
    }

    public function getTableRecordsPerPageSelectOptions(): array
    {
        return [5, 10, 25];
    }

    protected function getTableQuery(): Builder
    {
        return Drug::query()
            ->where('is_active', true)
            ->withSum(['batches as total_stock' => function ($query) {
                $query->where('quantity_available', '>', 0)
                    ->where('expiry_date', '>', now());
            }], 'quantity_available')
            ->having('total_stock', '<=', DB::raw('min_stock'));
    }

    protected function isTablePaginationEnabled(): bool
    {
        return true;
    }
}
