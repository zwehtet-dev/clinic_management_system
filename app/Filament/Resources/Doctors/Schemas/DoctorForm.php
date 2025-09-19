<?php

namespace App\Filament\Resources\Doctors\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DoctorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Doctor Information')
                    ->schema([
                        TextInput::make('public_id')
                            ->required()
                            ->unique(ignoreRecord: true),
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('specialization')
                            ->default(null)
                            ->placeholder('e.g., Cardiology, Pediatrics, General Medicine'),

                        TextInput::make('license_number')
                            ->label('Medical License Number')
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),

                        TextInput::make('phone')
                            ->maxLength(11)
                            ->default(null),

                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->required(),
                    ])
                    ->columns(2),
                Section::make('Additional Information')
                    ->schema([
                        Textarea::make('address')
                            ->rows(3)
                            ->columnSpanFull(),

                        Textarea::make('notes')
                            ->rows(4)
                            ->placeholder('Qualifications, experience, special notes...')
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),

            ]);
    }
}
