<?php

namespace App\Filament\Resources\AttendanceAssignments\Pages;

use App\Filament\Resources\AttendanceAssignments\AttendanceAssignmentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAttendanceAssignments extends ListRecords
{
    protected static string $resource = AttendanceAssignmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
