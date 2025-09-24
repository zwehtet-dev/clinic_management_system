<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('User Information')
                    ->schema([
                        TextEntry::make('name'),
                        TextEntry::make('email')
                            ->badge()
                            ->color('primary')
                            ->label('Email address'),
                        TextEntry::make('created_at')
                            ->dateTime()
                            ->placeholder('-'),
                        IconEntry::make('is_active')
                            ->label('Active'),
                        TextEntry::make('updated_at')
                            ->dateTime()
                            ->placeholder('-'),
                    ])
                    ->columns(3)
                    ->columnSpanFull()
            ]);
    }
}
