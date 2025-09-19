<?php

namespace App\Filament\Resources\Drugs\Schemas;

use App\Models\Drug;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;

class DrugInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Drug Information')
                    ->schema([
                        TextEntry::make('name')
                            ->size('lg')
                            ->weight('bold'),

                        TextEntry::make('catelog')
                            ->weight('medium')
                            ->color('primary'),

                        TextEntry::make('generic_name')
                            ->label('Generic Name'),

                        TextEntry::make('drugForm.name')
                            ->label('Drug Form')
                            ->badge()
                            ->color('info'),

                        TextEntry::make('strength_unit')
                            ->label('Strength')
                            ->getStateUsing(fn (Drug $record): string => $record->strength . ' ' . $record->unit),


                        TextEntry::make('min_stock')
                            ->label('Minimum Stock Alert'),

                        TextEntry::make('expire_alert')
                            ->label('Expire Alert')
                            ->suffix(' Day/s'),

                        TextEntry::make('is_active')
                            ->badge()
                            ->color(fn (bool $state): string => $state ? 'success' : 'danger')
                            ->formatStateUsing(fn (bool $state): string => $state ? 'Active' : 'Inactive'),

                        TextEntry::make('created_at')
                            ->label('Created')
                            ->dateTime(),
                    ])
                    ->columns(3)
                    ->columnSpanFull(),

                Section::make('Description')
                    ->schema([
                        TextEntry::make('description')
                            ->markdown()
                            ->columnSpanFull(),
                    ])
                    ->visible(fn (Drug $record): bool => !empty($record->description))
                    ->columnSpanFull(),


            ]);
    }
}
