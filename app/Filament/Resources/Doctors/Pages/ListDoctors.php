<?php

namespace App\Filament\Resources\Doctors\Pages;

use App\Exports\DoctorsExport;
use App\Exports\DoctorsTemplateExport;
use App\Filament\Resources\Doctors\DoctorResource;
use App\Imports\DoctorsImport;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Maatwebsite\Excel\Facades\Excel;

class ListDoctors extends ListRecords
{
    protected static string $resource = DoctorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('import')
                ->label('Import Doctors')
                ->icon(Heroicon::OutlinedDocumentArrowUp)
                ->color('success')
                ->form([
                    FileUpload::make('file')
                        ->label('Excel File')
                        ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel'])
                        ->required()
                        ->helperText('Upload an Excel file (.xlsx or .xls) with doctor data. Download the template from Settings > Import Templates.')
                ])
                ->action(function (array $data) {
                    try {
                        $import = new DoctorsImport;
                        Excel::import($import, $data['file']);
                        
                        $failures = $import->failures();
                        $errors = $import->errors();
                        
                        if (count($failures) > 0 || count($errors) > 0) {
                            $errorMessage = 'Import completed with some issues:';
                            foreach ($failures as $failure) {
                                $errorMessage .= "\nRow {$failure->row()}: " . implode(', ', $failure->errors());
                            }
                            foreach ($errors as $error) {
                                $errorMessage .= "\n" . $error->getMessage();
                            }
                            
                            Notification::make()
                                ->title('Import completed with warnings')
                                ->body($errorMessage)
                                ->warning()
                                ->persistent()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Import successful')
                                ->body('All doctors have been imported successfully.')
                                ->success()
                                ->send();
                        }
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Import failed')
                            ->body('Error: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
            
            Action::make('export')
                ->label('Export Doctors')
                ->icon(Heroicon::OutlinedDocumentArrowDown)
                ->color('warning')
                ->action(function () {
                    return Excel::download(new DoctorsExport, 'doctors_' . now()->format('Y-m-d_H-i-s') . '.xlsx');
                }),
            
            CreateAction::make(),
        ];
    }
}
