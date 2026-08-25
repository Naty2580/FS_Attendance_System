<?php

namespace App\Filament\Resources\AttendanceAssignments\Pages;

use App\Filament\Resources\AttendanceAssignments\AttendanceAssignmentResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditAttendanceAssignment extends EditRecord
{
    protected static string $resource = AttendanceAssignmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
