<?php

namespace App\Filament\Resources\ExpenseCategories\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;

class ExpenseCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Category Information')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),

                        Toggle::make('is_active')
                            ->default(true)
                            ->label('Active Category'),

                        Textarea::make('description')
                            ->rows(4)
                            ->columnSpanFull()
                            ->placeholder('Category description, what expenses this covers...'),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}
