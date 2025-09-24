<?php

namespace App\Filament\Resources\Doctors\Schemas;

use App\Models\Doctor;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Group;

class DoctorInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Doctor Information')
                    ->schema([
                        TextEntry::make('public_id')
                            ->label('Doctor ID')
                            ->badge()
                            ->color('primary'),

                        TextEntry::make('name')
                            ->size('lg')
                            ->weight('bold'),

                        TextEntry::make('specialization')
                            ->badge()
                            ->color('info'),

                        TextEntry::make('license_number')
                            ->label('Medical License')
                            ->copyable(),

                        TextEntry::make('phone')
                            ->icon('heroicon-o-phone')
                            ->copyable(),

                        TextEntry::make('is_active')
                            ->badge()
                            ->color(fn (bool $state): string => $state ? 'success' : 'danger')
                            ->formatStateUsing(fn (bool $state): string => $state ? 'Active' : 'Inactive'),

                        TextEntry::make('visits_count')
                            ->label('Total Visits')
                            ->getStateUsing(fn (Doctor $record): int => $record->visits()->count())
                            ->badge()
                            ->color('primary'),

                        TextEntry::make('this_month_visits')
                            ->label('Visits This Month')
                            ->getStateUsing(fn (Doctor $record): int =>
                                $record->visits()->whereMonth('visit_date', now()->month)->count()
                            )
                            ->badge()
                            ->color('success'),
                    ])
                    ->columns(2),

                Group::make([
                    Section::make('Contact Information')
                    ->schema([
                        TextEntry::make('address')
                            ->icon('heroicon-o-map-pin')
                            ->columnSpanFull(),
                    ])
                    ->visible(fn (Doctor $record): bool => !empty($record->address)),

                    Section::make('Notes')
                        ->schema([
                            TextEntry::make('notes')
                                ->markdown()
                                ->columnSpanFull(),
                        ])
                        ->visible(fn (Doctor $record): bool => !empty($record->notes)),
                ])
            ]);
    }
}
