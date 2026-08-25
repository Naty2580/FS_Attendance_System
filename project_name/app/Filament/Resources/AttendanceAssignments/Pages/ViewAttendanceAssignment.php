<?php

namespace App\Filament\Resources\AttendanceAssignments\Pages;

use App\Filament\Resources\AttendanceAssignments\AttendanceAssignmentResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewAttendanceAssignment extends ViewRecord
{
    protected static string $resource = AttendanceAssignmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
