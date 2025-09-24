<?php

namespace App\Filament\Resources\ExpenseCategories\RelationManagers;

use App\Filament\Resources\Expenses\ExpenseResource;
use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class ExpensesRelationManager extends RelationManager
{
    protected static string $relationship = 'expenses';

    protected static ?string $relatedResource = ExpenseResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->headerActions([
                CreateAction::make(),
            ]);
    }
}
