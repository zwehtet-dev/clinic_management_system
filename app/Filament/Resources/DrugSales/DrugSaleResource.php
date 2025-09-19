<?php

namespace App\Filament\Resources\DrugSales;

use App\Filament\Resources\DrugSales\Pages\CreateDrugSale;
use App\Filament\Resources\DrugSales\Pages\EditDrugSale;
use App\Filament\Resources\DrugSales\Pages\ListDrugSales;
use App\Filament\Resources\DrugSales\Pages\ViewDrugSale;
use App\Filament\Resources\DrugSales\Schemas\DrugSaleForm;
use App\Filament\Resources\DrugSales\Schemas\DrugSaleInfolist;
use App\Filament\Resources\DrugSales\Tables\DrugSalesTable;
use App\Models\DrugSale;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class DrugSaleResource extends Resource
{
    protected static ?string $model = DrugSale::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingCart;

    protected static string|UnitEnum|null $navigationGroup = 'Pharmacy';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Drug Sales';

    protected static ?string $recordTitleAttribute = 'public_id';

    public static function getGlobalSearchResultTitle($record): string
    {
        return "Sale {$record->public_id}";
    }

    public static function getGlobalSearchResultDetails($record): array
    {
        return [
            'Customer' => $record->buyer_display_name,
            'Date' => $record->sale_date->format('M d, Y'),
            'Amount' => '$' . number_format($record->total_amount, 2),
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return DrugSaleForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return DrugSaleInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DrugSalesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDrugSales::route('/'),
            'create' => CreateDrugSale::route('/create'),
            'view' => ViewDrugSale::route('/{record}'),
            'edit' => EditDrugSale::route('/{record}/edit'),
        ];
    }
}
