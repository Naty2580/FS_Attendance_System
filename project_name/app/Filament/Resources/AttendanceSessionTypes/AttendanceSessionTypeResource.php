<?php

namespace App\Filament\Resources\AttendanceSessionTypes;

use App\Filament\Resources\AttendanceSessionTypes\Pages\CreateAttendanceSessionType;
use App\Filament\Resources\AttendanceSessionTypes\Pages\EditAttendanceSessionType;
use App\Filament\Resources\AttendanceSessionTypes\Pages\ListAttendanceSessionTypes;
use App\Filament\Resources\AttendanceSessionTypes\Pages\ViewAttendanceSessionType;
use App\Filament\Resources\AttendanceSessionTypes\Schemas\AttendanceSessionTypeForm;
use App\Filament\Resources\AttendanceSessionTypes\Schemas\AttendanceSessionTypeInfolist;
use App\Filament\Resources\AttendanceSessionTypes\Tables\AttendanceSessionTypesTable;
use App\Models\AttendanceSessionType;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AttendanceSessionTypeResource extends Resource
{
    protected static ?string $model = AttendanceSessionType::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return AttendanceSessionTypeForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AttendanceSessionTypeInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AttendanceSessionTypesTable::configure($table);
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
            'index' => ListAttendanceSessionTypes::route('/'),
            'create' => CreateAttendanceSessionType::route('/create'),
            'view' => ViewAttendanceSessionType::route('/{record}'),
            'edit' => EditAttendanceSessionType::route('/{record}/edit'),
        ];
    }
}
