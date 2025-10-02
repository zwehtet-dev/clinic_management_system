<?php

namespace App\Filament\Resources\Services\Pages;

use App\Exports\ServicesExport;
use App\Exports\ServicesTemplateExport;
use App\Filament\Resources\Services\ServiceResource;
use App\Imports\ServicesImport;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Maatwebsite\Excel\Facades\Excel;

class ListServices extends ListRecords
{
    protected static string $resource = ServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('import')
                ->label('Import Services')
                ->icon(Heroicon::OutlinedDocumentArrowUp)
                ->color('success')
                ->form([
                    FileUpload::make('file')
                        ->label('Excel File')
                        ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel'])
                        ->required()
                        ->helperText('Upload an Excel file (.xlsx or .xls) with service data. Download the template from Settings > Import Templates.')
                ])
                ->action(function (array $data) {
                    try {
                        $import = new ServicesImport;
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
                                ->body('All services have been imported successfully.')
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
                ->label('Export Services')
                ->icon(Heroicon::OutlinedDocumentArrowDown)
                ->color('warning')
                ->action(function () {
                    return Excel::download(new ServicesExport, 'services_' . now()->format('Y-m-d_H-i-s') . '.xlsx');
                }),
            
            CreateAction::make(),
        ];
    }
}
