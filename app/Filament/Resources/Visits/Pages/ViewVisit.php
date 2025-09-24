<?php

namespace App\Filament\Resources\Visits\Pages;

use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use App\Filament\Resources\Visits\VisitResource;
use Filament\Support\Colors\Color;
use SebastianBergmann\CodeCoverage\Report\Html\Colors;

class ViewVisit extends ViewRecord
{
    protected static string $resource = VisitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Back to List')
                ->url($this->getResource()::getUrl('index'))
                ->color('gray')
                ->icon('heroicon-o-arrow-left')
                ->button(),
            Action::make('create_invoice')
                ->label('Create Invoice')
                ->button()
                ->hidden(fn() => (bool)$this->record->invoice)
                ->color(Color::Gray)
                ->url(fn() => route('filament.admin.resources.invoices.create', [
                    'visit_id' => $this->record->id,
                ])),
            EditAction::make(),
        ];
    }
}
