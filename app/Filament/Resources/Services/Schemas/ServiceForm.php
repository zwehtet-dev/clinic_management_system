<?php

namespace App\Filament\Resources\Services\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;

class ServiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Service Information')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('price')
                            ->required()
                            ->numeric()
                            ->suffix(' KS')
                            ->step(50)
                            ->minValue(0),

                        Toggle::make('is_active')
                            ->default(true)
                            ->label('Active Service')
                            ,

                        Textarea::make('description')
                            ->rows(4)
                            ->columnSpanFull()
                            ->placeholder('Service description, procedures included, duration, etc.'),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}
