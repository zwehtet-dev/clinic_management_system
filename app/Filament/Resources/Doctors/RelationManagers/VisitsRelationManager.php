<?php

namespace App\Filament\Resources\Doctors\RelationManagers;

use App\Filament\Resources\Visits\VisitResource;
use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class VisitsRelationManager extends RelationManager
{
    protected static string $relationship = 'visits';

    protected static ?string $relatedResource = VisitResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->headerActions([
                CreateAction::make(),
            ]);
    }
}
