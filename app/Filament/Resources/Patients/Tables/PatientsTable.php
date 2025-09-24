<?php

namespace App\Filament\Resources\Patients\Tables;

use App\Models\Patient;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;

class PatientsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('public_id')
                    ->label('Patient ID')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('medium'),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),
                TextColumn::make('age')
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->suffix(' years'),
                TextColumn::make('gender')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'male' => 'info',
                        'female' => 'success',
                        'other' => 'warning',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),
                TextColumn::make('phone')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('visits_count')
                    ->label('Visits')
                    ->counts('visits')
                    ->alignCenter()
                    ->badge()
                    ->color('primary'),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->boolean(),
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
                SelectFilter::make('gender')
                    ->options([
                        'male' => 'Male',
                        'female' => 'Female',
                        'other' => 'Other',
                    ])
                    ->native(false),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                // Action::make('new_visit')
                //     ->label('New Visit')
                //     ->icon('heroicon-o-plus')
                //     ->color('primary')
                //     ->url(fn (Patient $record): string => VisitResource::getUrl('create', ['patient_id' => $record->id]))
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at','desc');
    }
}
