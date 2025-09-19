<?php

namespace App\Filament\Resources\DailyReports\Tables;

use Filament\Tables\Table;
use App\Models\DailyReport;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Filters\Filter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Notifications\Notification;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;

class DailyReportsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('report_date')
                    ->date()
                    ->sortable()
                    ->weight('medium'),

                TextColumn::make('new_patients')
                    ->alignCenter()
                    ->badge()
                    ->color('info'),

                TextColumn::make('visits_count')
                    ->label('Visits')
                    ->alignCenter()
                    ->badge()
                    ->color('primary'),

                TextColumn::make('total_revenue')
                    ->suffix(' Ks')
                    ->alignCenter()
                    ->weight('medium')
                    ->color('success'),

                TextColumn::make('total_expenses')
                    ->suffix(' Ks')
                    ->alignCenter()
                    ->color('danger'),

                TextColumn::make('net_income')
                    ->suffix(' Ks')
                    ->alignCenter()
                    ->weight('bold')
                    ->color(fn (string $state): string =>
                        (float) str_replace([' Ks', ','], '', $state) >= 0 ? 'success' : 'danger'
                    ),

                TextColumn::make('drug_sales_count')
                    ->label('Drug Sales')
                    ->alignCenter()
                    ->badge()
                    ->color('warning')
                    ->toggleable(),

                TextColumn::make('expense_count')
                    ->label('Expenses')
                    ->alignCenter()
                    ->badge()
                    ->color('gray')
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('report_date')
                    ->form([
                        DatePicker::make('date_from')
                            ->label('Date from'),
                        DatePicker::make('date_until')
                            ->label('Date until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['date_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('report_date', '>=', $date),
                            )
                            ->when(
                                $data['date_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('report_date', '<=', $date),
                            );
                    }),

                Filter::make('profitable_days')
                    ->label('Profitable Days Only')
                    ->query(fn (Builder $query): Builder => $query->where('net_income', '>', 0)),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('regenerate')
                    ->label('Regenerate')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->action(function (DailyReport $record) {
                        DailyReport::generateReport($record->report_date);

                        Notification::make()
                            ->title('Report Regenerated')
                            ->success()
                            ->body('Daily report has been regenerated with current data')
                            ->send();
                    })
                    ->requiresConfirmation(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('report_date', 'desc');
    }
}
