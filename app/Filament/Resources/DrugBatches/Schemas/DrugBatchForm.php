<?php

namespace App\Filament\Resources\DrugBatches\Schemas;

use App\Models\Drug;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;

class DrugBatchForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Drug Information')
                    ->schema([
                        Select::make('drug_id')
                            ->label('Drug')
                            ->relationship('drug','name')
                            ->searchable()
                            ->preload(false)
                            ->getSearchResultsUsing(function (string $search) {
                                return Drug::active()
                                    ->where('name', 'like', "%{$search}%")
                                    ->orWhere('generic_name', 'like', "%{$search}%")
                                    ->limit(50)
                                    ->orderBy('name', 'asc')
                                    ->get()
                                    ->mapWithKeys(fn ($drug) => [$drug->id => "{$drug->name} - {$drug->generic_name}"])
                                    ->toArray();
                            })
                            ->required(),
                        TextInput::make('batch_number')
                            ->maxLength(100)
                            ->unique(ignoreRecord:true)
                            ->required(),
                        TextInput::make('purchase_price')
                            ->numeric()
                            ->suffix(' Ks')
                            ->default(null),
                        TextInput::make('sell_price')
                            ->required()
                            ->suffix(' Ks')
                            ->numeric(),
                        TextInput::make('quantity_received')
                            ->required()
                            ->numeric()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                $set('quantity_available', $state);
                            })
                            ->default(0),
                        TextInput::make('quantity_available')
                            ->required()
                            ->numeric()
                            ->dehydrated(true)
                            ->default(0)
                            ->disabled(fn (string $operation): bool => $operation === 'create'),
                        DatePicker::make('expiry_date')
                            ->required(),
                        DatePicker::make('received_date')
                    ])
                    ->columns(2)
                    ->columnSpanFull()
            ]);
    }
}
