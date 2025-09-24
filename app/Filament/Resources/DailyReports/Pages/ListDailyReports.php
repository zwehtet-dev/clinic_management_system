<?php

namespace App\Filament\Resources\DailyReports\Pages;

use Carbon\Carbon;
use App\Models\DailyReport;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\DailyReports\DailyReportResource;

class ListDailyReports extends ListRecords
{
    protected static string $resource = DailyReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('generate_range')
                ->label('Generate Date Range')
                ->icon('heroicon-o-calendar')
                ->color('gray')
                ->form([
                    DatePicker::make('start_date')
                        ->label('Start Date')
                        ->required(),
                    DatePicker::make('end_date')
                        ->label('End Date')
                        ->required()
                        ->after('start_date'),
                ])
                ->action(function (array $data) {
                    DailyReport::generateReportsForRange($data['start_date'], $data['end_date']);

                    Notification::make()
                        ->title('Reports Generated')
                        ->success()
                        ->body('Reports generated for selected date range')
                        ->send();
                }),
            // CreateAction::make(),
            Action::make('generate_today')
                ->label('Generate Today\'s Report')
                ->icon('heroicon-o-plus-circle')
                ->color('primary')
                ->action(function () {
                    DailyReport::generateReport(Carbon::today());

                    Notification::make()
                        ->title('Today\'s Report Generated')
                        ->success()
                        ->send();
                }),



        ];
    }
}
