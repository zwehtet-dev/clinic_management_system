<?php

namespace App\Filament\Resources\DrugBatches;

use App\Filament\Resources\DrugBatches\Pages\CreateDrugBatch;
use App\Filament\Resources\DrugBatches\Pages\EditDrugBatch;
use App\Filament\Resources\DrugBatches\Pages\ListDrugBatches;
use App\Filament\Resources\DrugBatches\Pages\ViewDrugBatch;
use App\Filament\Resources\DrugBatches\Schemas\DrugBatchForm;
use App\Filament\Resources\DrugBatches\Schemas\DrugBatchInfolist;
use App\Filament\Resources\DrugBatches\Tables\DrugBatchesTable;
use App\Models\DrugBatch;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class DrugBatchResource extends Resource
{
    protected static ?string $model = DrugBatch::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArchiveBox;
    protected static string|UnitEnum|null $navigationGroup = 'Pharmacy';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'batch_number';

    public static function getGlobalSearchResultTitle($record): string
    {
        return $record->batch_number;
    }

    public static function getGlobalSearchResultDetails($record): array
    {
        return [
            'Drug Name' => $record->drug->name,
            'Sell Price' => $record->sell_price,
            'Stock' => $record->quantity_available,
            'Expire Date' => $record->expiry_date
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::whereHas('drug')
            ->get()
            ->filter(fn ($batch) => $batch->is_expire_alert)
            ->count();

        return $count > 0 ? (string)$count : null;

    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return static::getNavigationBadge() > 0 ? 'danger' : 'primary';
    }

    public static function form(Schema $schema): Schema
    {
        return DrugBatchForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return DrugBatchInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DrugBatchesTable::configure($table);
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
            'index' => ListDrugBatches::route('/'),
            'create' => CreateDrugBatch::route('/create'),
            'view' => ViewDrugBatch::route('/{record}'),
            'edit' => EditDrugBatch::route('/{record}/edit'),
        ];
    }
}
