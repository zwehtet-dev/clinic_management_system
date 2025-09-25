<?php

namespace App\Filament\Resources\Doctors\Tables;

use App\Models\Doctor;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Colors\Color;
use Illuminate\Support\HtmlString;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Notifications\Notification;
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
                Action::make('markAllAsPaid')
                    ->label('Paid')
                    ->color(Color::Blue)
                    ->icon('heroicon-o-check-badge')
                    ->requiresConfirmation()
                    ->modalHeading('Confirm Payment')
                    ->modalContent(function ($record) {
                        // calculate total unpaid for this doctor
                        $totalUnpaid = $record->doctorReferrals()
                            ->where('status', 'unpaid')
                            ->sum('referral_fee');

                        return new HtmlString(
                            "<p style='text-align: center;'>Paid Amount: <span style='color: #228B22;' >{$totalUnpaid} Ks</span></p>"
                        );
                    })
                    ->action(function ($record) {
                        // calculate again for notification
                        $totalUnpaid = $record->doctorReferrals()
                            ->where('status', 'unpaid')
                            ->sum('referral_fee');

                        // mark all unpaid referrals as paid
                        $record->doctorReferrals()
                            ->where('status', 'unpaid')
                            ->update(['status' => 'paid']);

                        Notification::make()
                            ->title("Paid {$totalUnpaid} Ks to {$record->name}")
                            ->success()
                            ->send();
                    })
                    ->visible(fn($record)=>(bool) $record->doctorReferrals()->where('status', 'unpaid')->sum('referral_fee') > 0),
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
