<?php

namespace App\Filament\Resources\Patients\Schemas;

use App\Models\Patient;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PatientForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Patient Information')
                    ->schema([
                        TextInput::make('public_id')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->default(fn () => Patient::generatePublicId()),
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->autofocus(),
                        TextInput::make('age')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100),
                        Select::make('gender')
                            ->options(['male' => 'Male', 'female' => 'Female', 'other' => 'Other'])
                            ->native(false)
                            ->required(),
                        TextInput::make('phone')
                            ->default(null)
                            ->maxLength(11),
                        Group::make()
                            ->schema([
                                Textarea::make('address')
                                    ->default(null)
                                    ->rows(3),
                                Textarea::make('notes')
                                    ->default(null)
                                    ->rows(3)
                                    ->placeholder('Medical history, allergies, special notes...')
                            ])
                            ->columns(2)
                            ->columnSpanFull(),
                        Toggle::make('is_active')
                            ->label('Active')
                            ->required()
                            ->default(true),
                    ])
                    ->columns(3)
                    ->columnSpanFull(),
            ]);
    }
}
