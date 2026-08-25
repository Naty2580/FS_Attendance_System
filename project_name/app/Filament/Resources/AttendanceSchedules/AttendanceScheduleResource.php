<?php

namespace App\Filament\Resources\AttendanceSchedules;

use App\Filament\Resources\AttendanceSchedules\Pages\CreateAttendanceSchedule;
use App\Filament\Resources\AttendanceSchedules\Pages\EditAttendanceSchedule;
use App\Filament\Resources\AttendanceSchedules\Pages\ListAttendanceSchedules;
use App\Filament\Resources\AttendanceSchedules\Pages\ViewAttendanceSchedule;
use App\Filament\Resources\AttendanceSchedules\Schemas\AttendanceScheduleForm;
use App\Filament\Resources\AttendanceSchedules\Schemas\AttendanceScheduleInfolist;
use App\Filament\Resources\AttendanceSchedules\Tables\AttendanceSchedulesTable;
use App\Models\AttendanceSchedule;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AttendanceScheduleResource extends Resource
{
    protected static ?string $model = AttendanceSchedule::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return AttendanceScheduleForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AttendanceScheduleInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AttendanceSchedulesTable::configure($table);
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
            'index' => ListAttendanceSchedules::route('/'),
            'create' => CreateAttendanceSchedule::route('/create'),
            'view' => ViewAttendanceSchedule::route('/{record}'),
            'edit' => EditAttendanceSchedule::route('/{record}/edit'),
        ];
    }
}
