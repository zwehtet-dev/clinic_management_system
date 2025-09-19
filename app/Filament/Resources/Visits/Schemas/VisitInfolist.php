<?php

namespace App\Filament\Resources\Visits\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;

class VisitInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Visit Information')
                    ->schema([
                        TextEntry::make('public_id')
                            ->label('Visit ID')
                            ->badge()
                            ->color('primary'),

                        TextEntry::make('patient.name')
                            ->label('Patient')
                            ->weight('bold'),

                        TextEntry::make('doctor.name')
                            ->label('Doctor')
                            ->weight('bold'),

                        TextEntry::make('visit_type')
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => ucfirst(str_replace('-', ' ', $state))),

                        TextEntry::make('consultation_fee')
                            ->numeric()
                            ->suffix(' KS'),

                        TextEntry::make('visit_date')
                            ->date(),

                        TextEntry::make('status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'pending' => 'warning',
                                'completed' => 'success',
                                'cancelled' => 'danger',
                            }),

                        TextEntry::make('created_at')
                            ->label('Created')
                            ->dateTime(),
                    ])
                    ->columns(3),

                Section::make('Medical Details')
                    ->schema([
                        TextEntry::make('diagnosis')
                            ->markdown()
                            ->columnSpanFull(),

                        TextEntry::make('notes')
                            ->markdown()
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
