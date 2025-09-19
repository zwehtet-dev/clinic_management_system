<?php

namespace App\Filament\Resources\Invoices\Schemas;

use App\Models\Invoice;
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

                        TextEntry::make('invoice_date')
                            ->date(),

                        TextEntry::make('status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'paid' => 'success',
                                'cancelled' => 'danger',
                            }),

                        TextEntry::make('total_amount')
                            ->money('USD')
                            ->size('lg')
                            ->weight('bold'),

                        TextEntry::make('created_at')
                            ->label('Created')
                            ->dateTime(),
                    ])
                    ->columns(3),

                Section::make('Invoice Items')
                    ->schema([
                        RepeatableEntry::make('invoiceItems')
                            ->label('')
                            ->schema([
                                TextEntry::make('itemable.name')
                                    ->label('Item'),
                                TextEntry::make('quantity'),
                                TextEntry::make('unit_price')
                                    ->suffix(' KS'),
                                TextEntry::make('line_total')
                                    ->label('Total')
                                    ->suffix(' KS'),
                            ])
                            ->columns(4)
                            ->columnSpanFull(),
                    ]),

                Section::make('Notes')
                    ->schema([
                        TextEntry::make('notes')
                            ->markdown()
                            ->columnSpanFull(),
                    ])
                    ->visible(fn (Invoice $record): bool => !empty($record->notes)),
            ]);
    }
}
