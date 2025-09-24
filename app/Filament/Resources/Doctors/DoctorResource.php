<?php

namespace App\Filament\Resources\Doctors;

use BackedEnum;
use App\Models\Doctor;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use App\Filament\Resources\Doctors\Pages\EditDoctor;
use App\Filament\Resources\Doctors\Pages\ViewDoctor;
use App\Filament\Resources\Doctors\Pages\ListDoctors;
use App\Filament\Resources\Doctors\Pages\CreateDoctor;
use App\Filament\Resources\Doctors\Schemas\DoctorForm;
use App\Filament\Resources\Doctors\Tables\DoctorsTable;
use App\Filament\Resources\Doctors\Schemas\DoctorInfolist;
use App\Filament\Resources\Doctors\RelationManagers;
use App\Filament\Resources\Doctors\RelationManagers\VisitsRelationManager;
use App\Filament\Resources\Visits\VisitResource;
use UnitEnum;

class DoctorResource extends Resource
{
    protected static ?string $model = Doctor::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static string|UnitEnum|null $navigationGroup = 'Medical Services';

    protected static ?int $navigationSort = 4;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getGlobalSearchResultTitle($record): string
    {
        return $record->name;
    }

    public static function getGlobalSearchResultDetails($record): array
    {
        return [
            'ID' => $record->public_id,
            'Specialization' => $record->specialization ?? '-',
            'Phone' => $record->phone ?? '-' ,
        ];
    }


    public static function form(Schema $schema): Schema
    {
        return DoctorForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return DoctorInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DoctorsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            VisitsRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDoctors::route('/'),
            'create' => CreateDoctor::route('/create'),
            'view' => ViewDoctor::route('/{record}'),
            'edit' => EditDoctor::route('/{record}/edit'),
        ];
    }
}
