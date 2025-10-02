<?php

namespace App\Filament\Resources\Invoices\Tables;

use App\Models\Visit;
use App\Models\Invoice;
use App\Models\DrugSale;
use Filament\Tables\Table;
use Filament\Actions\Action;
use App\Jobs\PrintInvoiceJob;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Filters\Filter;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\Select;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;

class InvoicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('invoice_number')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('medium'),

                TextColumn::make('invoice_date')
                    ->date()
                    ->sortable(),

                TextColumn::make('total_amount')
                    ->numeric()
                    ->suffix(' KS')
                    ->sortable()
                    ->weight('medium'),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paid' => 'success',
                        'cancelled' => 'danger',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),

                TextColumn::make('invoiceable_type')
                    ->label('Type')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        Visit::class => 'Visit',
                        DrugSale::class => 'Drug Sale',
                        default => 'Other',
                    })
                    ->badge()
                    ->color('info'),

                TextColumn::make('items_count')
                    ->label('Items')
                    ->counts('invoiceItems')
                    ->alignCenter()
                    ->badge()
                    ->color('primary'),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'paid' => 'Paid',
                        'cancelled' => 'Cancelled',
                    ])
                    ->native(false),

                Filter::make('invoice_date')
                    ->form([
                        DatePicker::make('invoice_from')
                            ->label('Invoice from'),
                        DatePicker::make('invoice_until')
                            ->label('Invoice until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['invoice_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('invoice_date', '>=', $date),
                            )
                            ->when(
                                $data['invoice_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('invoice_date', '<=', $date),
                            );
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                // Action::make('print')
                //     ->label('Print')
                //     ->icon('heroicon-o-printer')
                //     ->color('primary')
                //     ->action(function (Invoice $record) {
                //         // Dispatch print job
                //         PrintInvoiceJob::dispatch($record);

                //         Notification::make()
                //             ->title('Print Queued')
                //             ->success()
                //             ->body("Invoice {$record->invoice_number} has been queued for printing")
                //             ->send();
                //     })
                //     ->requiresConfirmation()
                //     ->modalHeading('Print Invoice')
                //     ->modalDescription('This will send the invoice to your configured printer.')
                //     ->modalSubmitActionLabel('Print Now'),
                // Action::make('print_options')
                //     ->label('Print Options')
                //     ->icon('heroicon-o-cog-6-tooth')
                //     ->color('gray')
                //     ->form([
                //         Select::make('printer_type')
                //             ->label('Printer Type')
                //             ->options([
                //                 'thermal' => 'Thermal Receipt Printer',
                //                 'regular' => 'Regular Printer (A4)',
                //                 'network' => 'Network Printer',
                //             ])
                //             ->default(config('printing.default_printer_type'))
                //             ->required(),

                //         TextInput::make('copies')
                //             ->label('Number of Copies')
                //             ->numeric()
                //             ->default(1)
                //             ->minValue(1)
                //             ->maxValue(5),
                //     ])
                //     ->action(function (Invoice $record, array $data) {
                //         PrintInvoiceJob::dispatch($record, $data);

                //         Notification::make()
                //             ->title('Print Queued')
                //             ->success()
                //             ->body("Invoice {$record->invoice_number} queued for {$data['printer_type']} printer ({$data['copies']} copies)")
                //             ->send();
                //     })
            ])

            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('invoice_date','desc');
    }
}
