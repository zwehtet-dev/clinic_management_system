<?php

namespace App\Filament\Resources\Drugs;

use App\Filament\Resources\Drugs\Pages\CreateDrug;
use App\Filament\Resources\Drugs\Pages\EditDrug;
use App\Filament\Resources\Drugs\Pages\ListDrugs;
use App\Filament\Resources\Drugs\Pages\ViewDrug;
use App\Filament\Resources\Drugs\Schemas\DrugForm;
use App\Filament\Resources\Drugs\Schemas\DrugInfolist;
use App\Filament\Resources\Drugs\Tables\DrugsTable;
use App\Filament\Resources\Drugs\RelationManagers;
use App\Filament\Resources\Drugs\RelationManagers\BatchesRelationManager;
use App\Models\Drug;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class DrugResource extends Resource
{
    protected static ?string $model = Drug::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBeaker;

    protected static string|UnitEnum|null $navigationGroup = 'Pharmacy';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getGlobalSearchResultTitle($record): string
    {
        return $record->name;
    }

    public static function getGlobalSearchResultDetails($record): array
    {
        return [
            'Generic Name' => $record->generic_name,
            'Catelog' => $record->catelog
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::withSum(['batches as total_stock' => function ($query) {
                $query->where('quantity_available', '>', 0)
                    ->where('expiry_date', '>', now());
            }], 'quantity_available')
            ->where('is_active', true)
            ->get()
            ->filter(fn ($drug) => $drug->total_stock <= $drug->min_stock)
            ->count();

        return $count > 0 ? (string)$count : null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return static::getNavigationBadge() > 0 ? 'danger' : 'primary';
    }

    public static function form(Schema $schema): Schema
    {
        return DrugForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return DrugInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DrugsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            BatchesRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDrugs::route('/'),
            'create' => CreateDrug::route('/create'),
            'view' => ViewDrug::route('/{record}'),
            'edit' => EditDrug::route('/{record}/edit'),
        ];
    }
}
