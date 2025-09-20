<?php

namespace App\Filament\Resources\Drugs\RelationManagers;

use App\Filament\Resources\Drugs\DrugResource;
use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class BatchesRelationManager extends RelationManager
{
    protected static string $relationship = 'batches';

    protected static ?string $relatedResource = DrugResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->headerActions([
                CreateAction::make(),
            ]);
    }
}
