<?php

namespace App\Filament\Resources\DrugSales\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class DrugSaleInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('public_id'),
                TextEntry::make('patient_id')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('buyer_name')
                    ->placeholder('-'),
                TextEntry::make('sale_date')
                    ->date(),
                TextEntry::make('total_amount')
                    ->numeric(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
