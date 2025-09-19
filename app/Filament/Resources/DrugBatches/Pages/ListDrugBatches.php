<?php

namespace App\Filament\Resources\DrugBatches\Pages;

use App\Filament\Resources\DrugBatches\DrugBatchResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDrugBatches extends ListRecords
{
    protected static string $resource = DrugBatchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
