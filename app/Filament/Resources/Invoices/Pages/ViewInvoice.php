<?php

namespace App\Filament\Resources\Invoices\Pages;

use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use App\Filament\Resources\Invoices\InvoiceResource;

class ViewInvoice extends ViewRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('print_receipt')
                ->label('Print Receipt')
                ->icon('heroicon-o-printer')
                ->color('success')
                ->action(function () {
                    try {
                        $printUrl = app(\App\Services\PrinterService::class)->printInvoice($this->record, ['format' => 'receipt']);
                        $this->js("window.open('{$printUrl}', '_blank', 'width=400,height=600,scrollbars=yes');");
                    } catch (\Exception $e) {
                        \Filament\Notifications\Notification::make()
                            ->title('Print Error')
                            ->body('Failed to generate print URL: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Action::make('print_thermal')
                ->label('Thermal Print')
                ->icon('heroicon-o-printer')
                ->color('warning')
                ->action(function () {
                    try {
                        $printUrl = app(\App\Services\PrinterService::class)->printThermalReceipt($this->record);
                        $this->js("window.open('{$printUrl}', '_blank', 'width=300,height=500,scrollbars=yes');");
                    } catch (\Exception $e) {
                        \Filament\Notifications\Notification::make()
                            ->title('Print Error')
                            ->body('Failed to generate thermal print URL: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Action::make('print_a4')
                ->label('A4 Invoice')
                ->icon('heroicon-o-document-text')
                ->color('info')
                ->action(function () {
                    try {
                        $printUrl = app(\App\Services\PrinterService::class)->printA4Invoice($this->record);
                        $this->js("window.open('{$printUrl}', '_blank', 'width=800,height=600,scrollbars=yes');");
                    } catch (\Exception $e) {
                        \Filament\Notifications\Notification::make()
                            ->title('Print Error')
                            ->body('Failed to generate A4 print URL: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
                
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
