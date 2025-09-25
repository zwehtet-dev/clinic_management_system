<?php

namespace App\Filament\Resources\DrugSales\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DrugSaleInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Drug Sale Information')
                    ->schema([
                        TextEntry::make('public_id')
                            ->badge(),
                        TextEntry::make('buyer_display_name')
                            ->placeholder('-'),
                        TextEntry::make('sale_date')
                            ->date(),
                        TextEntry::make('total_amount')
                            ->numeric()
                            ->suffix(' Ks'),
                        TextEntry::make('created_at')
                            ->dateTime()
                            ->placeholder('-'),
                    ])
                    ->columns(4)
                    ->columnSpanFull(),
            ]);
    }
}
