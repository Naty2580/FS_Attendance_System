<?php

namespace App\Filament\Resources\AttendanceSchedules\Pages;

use App\Filament\Resources\AttendanceSchedules\AttendanceScheduleResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewAttendanceSchedule extends ViewRecord
{
    protected static string $resource = AttendanceScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
