<?php

namespace App\Filament\Resources\AttendanceSchedules\Pages;

use App\Filament\Resources\AttendanceSchedules\AttendanceScheduleResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditAttendanceSchedule extends EditRecord
{
    protected static string $resource = AttendanceScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
