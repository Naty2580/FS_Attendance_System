<?php

namespace App\Filament\Resources\AttendanceAssignments;

use App\Filament\Resources\AttendanceAssignments\Pages\CreateAttendanceAssignment;
use App\Filament\Resources\AttendanceAssignments\Pages\EditAttendanceAssignment;
use App\Filament\Resources\AttendanceAssignments\Pages\ListAttendanceAssignments;
use App\Filament\Resources\AttendanceAssignments\Pages\ViewAttendanceAssignment;
use App\Filament\Resources\AttendanceAssignments\Schemas\AttendanceAssignmentForm;
use App\Filament\Resources\AttendanceAssignments\Schemas\AttendanceAssignmentInfolist;
use App\Filament\Resources\AttendanceAssignments\Tables\AttendanceAssignmentsTable;
use App\Models\AttendanceAssignment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AttendanceAssignmentResource extends Resource
{
    protected static ?string $model = AttendanceAssignment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return AttendanceAssignmentForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AttendanceAssignmentInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AttendanceAssignmentsTable::configure($table);
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
            'index' => ListAttendanceAssignments::route('/'),
            'create' => CreateAttendanceAssignment::route('/create'),
            'view' => ViewAttendanceAssignment::route('/{record}'),
            'edit' => EditAttendanceAssignment::route('/{record}/edit'),
        ];
    }
}