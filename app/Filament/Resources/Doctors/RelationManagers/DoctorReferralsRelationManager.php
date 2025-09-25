<?php

namespace App\Filament\Resources\Doctors\RelationManagers;

use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Schemas\Schema;
use App\Models\DoctorReferral;
use Filament\Actions\BulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Illuminate\Support\HtmlString;
use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\Select;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Actions\DissociateBulkAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Colors\Color;

class DoctorReferralsRelationManager extends RelationManager
{
    protected static string $relationship = 'doctorReferrals';

    protected function getTableQuery(): Builder
    {
        // Make sure ownerRecord exists
        if (! $this->ownerRecord) {
            return DoctorReferral::query(); // fallback to avoid null
        }

        return $this->ownerRecord->doctorReferrals()->getQuery();
    }


    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('visit_id')
                    ->label('Visit')
                    ->relationship('visit', 'public_id')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->getOptionLabelFromRecordUsing(fn ($record) =>
                        "{$record->public_id} - {$record->patient->name} ({$record->visit_date->format('M d, Y')})"
                    ),
                TextInput::make('referral_fee')
                    ->required()
                    ->suffix(' Ks')
                    ->numeric(),
                Select::make('status')
                    ->options(['unpaid' => 'Unpaid', 'paid' => 'Paid', 'cancelled' => 'Cancelled'])
                    ->default('unpaid')
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('visit.public_id')
            ->columns([
                TextColumn::make('visit.public_id')
                    ->label('Visit ID')
                    ->searchable(),
                TextColumn::make('visit.patient.name')
                    ->label('Patient')
                    ->searchable(),
                TextColumn::make('visit.visit_date')
                    ->label('Visit Date')
                    ->date()
                    ->sortable(),
                TextColumn::make('referral_fee')
                    ->numeric()
                    ->suffix(' Ks')
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'unpaid' => 'warning',
                        'paid' => 'success',
                        'cancelled' => 'danger',
                    }),
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
                        'unpaid' => 'Unpaid',
                        'paid' => 'Paid',
                        'cancelled' => 'Cancelled',
                    ]),

            ])
            ->headerActions([
                CreateAction::make(),
                AssociateAction::make(),
                Action::make('markAllAsPaid')
                    ->label('Paid')
                    ->color(Color::Gray)
                    ->requiresConfirmation()
                    ->modalHeading('Confirm Payment')
                    ->modalContent(function () {
                        // calculate total unpaid for this doctor
                        $totalUnpaid = $this->getTableQuery()->where('status', 'unpaid')->sum('referral_fee');

                        return new HtmlString(
                            "<p style='text-align: center;'>Paid Amount: <span style='color: #228B22;' >{$totalUnpaid} Ks</span></p>"
                        );

                        // return "You are about to mark all unpaid referrals as paid (Total: {$totalUnpaid} Ks). Are you sure?";
                    })
                    ->action(function () {
                        // update unpaid referrals
                        $totalUnpaid = $this->getTableQuery()->where('status', 'unpaid')->sum('referral_fee');
                        $this->getTableQuery()->where('status', 'unpaid')->update(['status' => 'paid']);

                        Notification::make()
                            ->title("Paid {$totalUnpaid} Ks")
                            ->success()
                            ->send();
                    }),


            ])
            ->recordActions([
                Action::make('markAsPaid')
                    ->label('Mark as Paid')
                    ->icon('heroicon-o-currency-dollar')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->status === 'unpaid')
                    ->action(function ($record) {
                        $record->update(['status' => 'paid']);

                        Notification::make()
                            ->title('Referral ( ' . $record->referral_fee . ' Ks ) marked as paid')
                            ->success()
                            ->send();
                    }),
                Action::make('markAsCancelled')
                    ->label('Mark as Cancelled')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->status === 'unpaid')
                    ->action(function ($record) {
                        $record->update(['status' => 'cancelled']);

                        Notification::make()
                            ->title('Referral ( ' . $record->referral_fee . ' Ks ) marked as cancelled')
                            ->success()
                            ->send();
                    }),
                EditAction::make(),
                DissociateAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DissociateBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }


}
