<?php

namespace App\Filament\Resources\DrugSales\Pages;

use App\Filament\Resources\DrugSales\DrugSaleResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewDrugSale extends ViewRecord
{
    protected static string $resource = DrugSaleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
