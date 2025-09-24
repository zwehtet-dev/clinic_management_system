<?php

namespace App\Filament\Resources\Doctors\Tables;

use App\Models\Doctor;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use App\Filament\Resources\Visits\VisitResource;

class DoctorsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('public_id')
                    ->label('Doctor ID')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('medium'),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),
                TextColumn::make('specialization')
                     ->searchable()
                    ->badge()
                    ->color('info')
                    ->limit(20),
                TextColumn::make('license_number')
                    ->label('License #')
                    ->searchable()
                    ->copyable()
                    ->toggleable(),
                TextColumn::make('phone')
                    ->searchable()
                    ->copyable()
                    ->toggleable(),
                TextColumn::make('visits_count')
                    ->label('Total Visits')
                    ->counts('visits')
                    ->alignCenter()
                    ->badge()
                    ->color('primary'),
                TextColumn::make('this_month_visits')
                    ->label('This Month')
                    ->getStateUsing(fn (Doctor $record): int =>
                        $record->visits()->whereMonth('visit_date', now()->month)->count()
                    )
                    ->alignCenter()
                    ->badge()
                    ->color('success'),
                IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueColor('success')
                    ->falseColor('danger')
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
                SelectFilter::make('specialization')
                    ->options(function () {
                        return Doctor::whereNotNull('specialization')
                            ->distinct()
                            ->pluck('specialization', 'specialization')
                            ->toArray();
                    })
                    ->searchable(),
                SelectFilter::make('is_active')
                    ->label('Status')
                    ->options([
                        1 => 'Active',
                        0 => 'Inactive',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                // Action::make('new_visit')
                //     ->label('New Visit')
                //     ->icon('heroicon-o-plus')
                //     ->color('primary')
                //     ->url(fn (Doctor $record): string => VisitResource::getUrl('create', ['doctor_id' => $record->id])),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at','desc');
    }
}
