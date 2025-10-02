<?php

namespace App\Filament\Resources\Drugs\Schemas;

use Dom\Text;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;

class DrugForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make('Drug Information')
                    ->schema([

                        TextInput::make('public_id')
                            ->label('Drug ID')
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('Auto-generated on save')
                            ->helperText('Unique drug ID will be generated automatically'),

                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('catelog')
                            ->maxLength(100),

                        TextInput::make('generic_name')
                            ->maxLength(255),

                        Select::make('drug_form_id')
                            ->label('Drug Form')
                            ->relationship('drugForm', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),
                                Textarea::make('description')
                                    ->rows(3),
                                Toggle::make('is_active')
                                    ->default(true),
                            ]),

                        TextInput::make('strength')
                            ->default(null)
                            ->maxLength(255),

                        TextInput::make('unit')
                            ->default(null)
                            ->maxLength(255),

                        Toggle::make('is_active')
                                ->default(true)
                                ->label('Active Drug'),

                    ])
                    ->columns(2),

                Group::make([
                    Section::make('Stock Management')
                        ->schema([

                            TextInput::make('min_stock')
                                ->label('Minimum Stock Alert')
                                ->required()
                                ->numeric()
                                ->default(10)
                                ->helperText('You will be notified when stock reaches this level'),

                            TextInput::make('expire_alert')
                                ->label('Expire Alert')
                                ->required()
                                ->numeric()
                                ->suffix('Day/s')
                                ->default(10)
                                ->helperText('You will be notified when the expiry date is within this number of days.'),
                        ])
                        ->columns(2),

                    Section::make('Additional Information')
                        ->schema([
                            Textarea::make('description')
                                ->rows(4)
                                ->columnSpanFull()
                                ->placeholder('Drug description, usage instructions, side effects, etc.'),
                        ])
                        ->collapsible(),
                ])

            ]);
    }
}
