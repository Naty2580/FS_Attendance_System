<?php

namespace App\Filament\Resources\ClassLevels;

use App\Filament\Resources\ClassLevels\Pages\CreateClassLevel;
use App\Filament\Resources\ClassLevels\Pages\EditClassLevel;
use App\Filament\Resources\ClassLevels\Pages\ListClassLevels;
use App\Filament\Resources\ClassLevels\Pages\ViewClassLevel;
use App\Filament\Resources\ClassLevels\Schemas\ClassLevelForm;
use App\Filament\Resources\ClassLevels\Schemas\ClassLevelInfolist;
use App\Filament\Resources\ClassLevels\Tables\ClassLevelsTable;
use App\Models\ClassLevel;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ClassLevelResource extends Resource
{
    protected static ?string $model = ClassLevel::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return ClassLevelForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ClassLevelInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ClassLevelsTable::configure($table);
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
            'index' => ListClassLevels::route('/'),
            'create' => CreateClassLevel::route('/create'),
            'view' => ViewClassLevel::route('/{record}'),
            'edit' => EditClassLevel::route('/{record}/edit'),
        ];
    }
}
