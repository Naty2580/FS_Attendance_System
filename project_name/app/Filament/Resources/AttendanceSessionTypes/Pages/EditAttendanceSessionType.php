<?php

namespace App\Filament\Resources\AttendanceSessionTypes\Pages;

use App\Filament\Resources\AttendanceSessionTypes\AttendanceSessionTypeResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditAttendanceSessionType extends EditRecord
{
    protected static string $resource = AttendanceSessionTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
