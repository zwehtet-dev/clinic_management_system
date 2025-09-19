<?php

namespace App\Filament\Resources\DrugBatches\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;

class DrugBatchInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Drug Batch Information')
                    ->schema([
                        TextEntry::make('drug.name')
                            ->color('primary'),
                        TextEntry::make('batch_number'),
                        TextEntry::make('purchase_price')
                            ->numeric()
                            ->placeholder('-'),
                        TextEntry::make('sell_price')
                            ->numeric(),
                        TextEntry::make('quantity_received')
                            ->numeric(),
                        TextEntry::make('quantity_available')
                            ->numeric(),
                        TextEntry::make('expiry_date')
                            ->date(),
                        TextEntry::make('received_date')
                            ->date()
                            ->placeholder('-'),
                        TextEntry::make('created_at')
                            ->dateTime()
                            ->placeholder('-'),
                        TextEntry::make('updated_at')
                            ->dateTime()
                            ->placeholder('-'),
                    ])
                    ->columns(2)
                    ->columnSpanFull()
            ]);
    }
}
