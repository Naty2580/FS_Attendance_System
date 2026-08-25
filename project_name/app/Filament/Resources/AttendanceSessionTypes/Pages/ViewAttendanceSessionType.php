<?php

namespace App\Filament\Resources\AttendanceSessionTypes\Pages;

use App\Filament\Resources\AttendanceSessionTypes\AttendanceSessionTypeResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewAttendanceSessionType extends ViewRecord
{
    protected static string $resource = AttendanceSessionTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
