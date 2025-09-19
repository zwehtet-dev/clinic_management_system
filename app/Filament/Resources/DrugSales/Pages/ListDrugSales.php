<?php

namespace App\Filament\Resources\DrugSales\Pages;

use App\Filament\Resources\DrugSales\DrugSaleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDrugSales extends ListRecords
{
    protected static string $resource = DrugSaleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
