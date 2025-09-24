<?php

namespace App\Filament\Resources\Patients\RelationManagers;

use App\Filament\Resources\DrugSales\DrugSaleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class DrugsalesRelationManager extends RelationManager
{
    protected static string $relationship = 'drugsales';

    protected static ?string $relatedResource = DrugSaleResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->headerActions([
                CreateAction::make(),
            ]);
    }
}
