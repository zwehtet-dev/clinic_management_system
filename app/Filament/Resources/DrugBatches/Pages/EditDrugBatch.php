<?php

namespace App\Filament\Resources\DrugBatches\Pages;

use App\Filament\Resources\DrugBatches\DrugBatchResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditDrugBatch extends EditRecord
{
    protected static string $resource = DrugBatchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
