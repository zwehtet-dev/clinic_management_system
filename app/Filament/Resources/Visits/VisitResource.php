<?php

namespace App\Filament\Resources\Visits;

use App\Filament\Resources\Visits\Pages\CreateVisit;
use App\Filament\Resources\Visits\Pages\EditVisit;
use App\Filament\Resources\Visits\Pages\ListVisits;
use App\Filament\Resources\Visits\Pages\ViewVisit;
use App\Filament\Resources\Visits\RelationManagers\InvoiceRelationManager;
use App\Filament\Resources\Visits\Schemas\VisitForm;
use App\Filament\Resources\Visits\Schemas\VisitInfolist;
use App\Filament\Resources\Visits\Tables\VisitsTable;
use App\Models\Visit;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class VisitResource extends Resource
{
    protected static ?string $model = Visit::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static string|UnitEnum|null $navigationGroup = 'Medical Services';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'public_id';

    public static function getGlobalSearchResultTitle($record): string
    {
        return "Visit {$record->public_id}";
    }

    public static function getGlobalSearchResultDetails($record): array
    {
        return [
            'Patient' => $record->patient->name,
            'Doctor' => $record->doctor->name,
            'Date' => $record->visit_date->format('M d, Y'),
            'Status' => ucfirst($record->status),
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return VisitForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return VisitInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return VisitsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            InvoiceRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListVisits::route('/'),
            'create' => CreateVisit::route('/create'),
            'view' => ViewVisit::route('/{record}'),
            'edit' => EditVisit::route('/{record}/edit'),
        ];
    }
}
