<?php

namespace App\Filament\Resources\Visits\Tables;

use App\Models\Visit;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use BladeUI\Icons\Components\Icon;
use Filament\Tables\Filters\Filter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;

class VisitsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('public_id')
                    ->label('Visit ID')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('medium'),
                TextColumn::make('patient.name')
                    ->label('Patient')
                    ->searchable()
                    ->weight('medium')
                    ->sortable(),
                TextColumn::make('doctor.name')
                    ->label('Doctor')
                    ->searchable()
                    ->weight('medium')
                    ->sortable(),
                TextColumn::make('visit_type')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'consultation' => 'primary',
                        'follow-up' => 'success',
                        default => 'secondary',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),
                TextColumn::make('consultation_fee')
                    ->numeric()
                    ->suffix(' KS')
                    ->sortable(),
                TextColumn::make('visit_date')
                    ->date()
                    ->sortable(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),
                IconColumn::make('has_invoice')
                    ->label('Invoice')
                    ->getStateUsing(fn ($record) => $record->invoice !== null)
                    ->boolean()
                    ->trueIcon('heroicon-o-document-text')
                    ->falseIcon('heroicon-o-document')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->alignCenter(),
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
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ])
                    ->native(false),
                SelectFilter::make('visit_type')
                    ->options([
                        'consultation' => 'Consultation',
                        'follow-up' => 'Follow-up',
                    ])
                    ->native(false),
                Filter::make('visit_date')
                    ->form([
                        DatePicker::make('visit_from')
                            ->label('Visit from'),
                        DatePicker::make('visit_until')
                            ->label('Visit until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['visit_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('visit_date', '>=', $date),
                            )
                            ->when(
                                $data['visit_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('visit_date', '<=', $date),
                            );
                    }),

            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                // Action::make('create_invoice')
                //     ->label('Create Invoice')
                //     ->icon('heroicon-o-document-text')
                //     ->color('primary')
                //     ->visible(fn (Visit $record): bool => !$record->invoices()->exists())
                //     ->url(fn (Visit $record): string => InvoiceResource::getUrl('create', ['visit_id' => $record->id])),

            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('visit_date', 'desc');
    }
}
