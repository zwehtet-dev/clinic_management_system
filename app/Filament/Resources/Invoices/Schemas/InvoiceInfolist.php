<?php

namespace App\Filament\Resources\Invoices\Schemas;

use App\Models\Invoice;
use App\Models\Service;
use App\Models\DrugBatch;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry;

class InvoiceInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Invoice Information')
                    ->schema([
                        TextEntry::make('invoice_number')
                            ->label('Invoice Number')
                            ->badge()
                            ->color('primary')
                            ->size('lg'),

                        TextEntry::make('invoiceable_type')
                            ->label('Invoice Type')
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                \App\Models\Visit::class => 'Visit Invoice',
                                \App\Models\DrugSale::class => 'Drug Sale Invoice',
                                default => 'Unknown Type',
                            })
                            ->badge()
                            ->color('info'),

                        TextEntry::make('invoiceable')
                            ->label('Reference')
                            ->formatStateUsing(function ($record) {
                                if ($record->invoiceable_type === \App\Models\Visit::class) {
                                    return $record->invoiceable->public_id . ' - ' . $record->invoiceable->patient->name;
                                } elseif ($record->invoiceable_type === \App\Models\DrugSale::class) {
                                    $customer = $record->invoiceable->patient
                                        ? $record->invoiceable->patient->name
                                        : $record->invoiceable->buyer_name;
                                    return $record->invoiceable->public_id . ' - ' . $customer;
                                }
                                return 'Unknown Reference';
                            }),

                        TextEntry::make('invoice_date')
                            ->date(),

                        TextEntry::make('status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'paid' => 'success',
                                'cancelled' => 'danger',
                            }),

                        TextEntry::make('total_amount')
                            ->suffix(' Ks')
                            ->size('lg')
                            ->weight('bold'),

                        TextEntry::make('created_at')
                            ->label('Created')
                            ->dateTime(),
                    ])
                    ->columns(3)
                    ->columnSpanFull(),

                // Drug Items Section
                Section::make('Drug Items')
                    ->schema([
                        RepeatableEntry::make('drugItems')
                            ->label('')
                            ->schema([
                                TextEntry::make('itemable')
                                    ->label('Drug/Batch')
                                    ->formatStateUsing(function ($record) {
                                        if ($record->itemable_type === DrugBatch::class) {
                                            $batch = $record->itemable;
                                            return "#{$batch->batch_number} - {$batch->drug->name}";
                                        }
                                        return $record->itemable->name ?? 'Unknown Item';
                                    }),
                                TextEntry::make('quantity')
                                    ->label('Qty'),
                                TextEntry::make('unit_price')
                                    ->label('Unit Price')
                                    ->suffix(' Ks'),
                                TextEntry::make('line_total')
                                    ->label('Total')
                                    ->suffix(' Ks')
                                    ->weight('bold'),
                            ])
                            ->columns(4)
                            ->columnSpanFull(),
                    ])
                    ->visible(fn (Invoice $record): bool => $record->invoiceItems()->whereHasMorph('itemable', [DrugBatch::class])->exists())
                    ->columnSpanFull(),

                // Services Section
                Section::make('Medical Services')
                    ->schema([
                        RepeatableEntry::make('serviceItems')
                            ->label('')
                            ->schema([
                                TextEntry::make('itemable.name')
                                    ->label('Service'),
                                TextEntry::make('itemable.description')
                                    ->label('Description')
                                    ->limit(50),
                                TextEntry::make('unit_price')
                                    ->label('Price')
                                    ->suffix(' Ks'),
                                TextEntry::make('line_total')
                                    ->label('Total')
                                    ->suffix(' Ks')
                                    ->weight('bold'),
                            ])
                            ->columns(4)
                            ->columnSpanFull(),
                    ])
                    ->visible(fn (Invoice $record): bool => $record->invoiceItems()->whereHasMorph('itemable', [Service::class])->exists())
                    ->columnSpanFull(),

                Section::make('Notes')
                    ->schema([
                        TextEntry::make('notes')
                            ->markdown()
                            ->columnSpanFull(),
                    ])
                    ->visible(fn (Invoice $record): bool => !empty($record->notes))
                    ->columnSpanFull(),
            ]);
    }
}
