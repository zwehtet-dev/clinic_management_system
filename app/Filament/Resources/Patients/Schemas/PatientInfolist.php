<?php

namespace App\Filament\Resources\Patients\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;

class PatientInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Patient Information')
                    ->schema([
                        TextEntry::make('public_id')
                            ->label('Patient ID')
                            ->badge()
                            ->color('primary'),
                        TextEntry::make('name')
                            ->size('lg')
                            ->weight('bold'),
                        TextEntry::make('age')
                            ->numeric()
                            ->suffix(' years'),
                        TextEntry::make('gender')
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => ucfirst($state)),
                        TextEntry::make('phone')
                            ->icon('heroicon-o-phone')
                            ->placeholder('-'),
                        TextEntry::make('address')
                            ->icon('heroicon-o-map-pin')
                            ->placeholder('-')
                            ->columnSpanFull(),
                        TextEntry::make('notes')
                            ->placeholder('-')
                            ->columnSpanFull(),
                        IconEntry::make('is_active')
                            ->boolean()
                            ->color(fn (bool $state): string => $state ? 'success' : 'danger')
                            ->formatStateUsing(fn (bool $state): string => $state ? 'Active' : 'Inactive'),
                        TextEntry::make('created_at')
                            ->label('Registered On')
                            ->dateTime()
                            ->placeholder('-'),
                    ])
                    ->columns(3),
            ]);
    }
}
