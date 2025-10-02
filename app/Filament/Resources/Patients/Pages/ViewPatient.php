<?php

namespace App\Filament\Resources\Patients\Pages;

use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Support\Colors\Color;
use Filament\Resources\Pages\ViewRecord;
use App\Filament\Resources\Patients\PatientResource;

class ViewPatient extends ViewRecord
{
    protected static string $resource = PatientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Back to List')
                ->url($this->getResource()::getUrl('index'))
                ->color('gray')
                ->icon('heroicon-o-arrow-left')
                ->button(),
            Action::make('create_visit')
                ->label('Create Visit')
                ->button()
                ->color(Color::Gray)
                ->url(fn() => route('filament.admin.resources.visits.create', [
                    'patient_id' => $this->record->id,
                ])),
            EditAction::make(),
        ];
    }
}
