<?php

namespace App\Filament\Resources\AttendanceSessionTypes\Pages;

use App\Filament\Resources\AttendanceSessionTypes\AttendanceSessionTypeResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAttendanceSessionType extends CreateRecord
{
    protected static string $resource = AttendanceSessionTypeResource::class;
}
