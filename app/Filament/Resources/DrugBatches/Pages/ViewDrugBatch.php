<?php

namespace App\Filament\Resources\DrugBatches\Pages;

use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use App\Filament\Resources\DrugBatches\DrugBatchResource;

class ViewDrugBatch extends ViewRecord
{
    protected static string $resource = DrugBatchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Back to List')
                ->url($this->getResource()::getUrl('index'))
                ->color('gray')
                ->icon('heroicon-o-arrow-left')
                ->button(),
            EditAction::make(),
        ];
    }
}
