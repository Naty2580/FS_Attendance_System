<?php

namespace App\Filament\Resources\AttendanceSessionTypes\Pages;

use App\Filament\Resources\AttendanceSessionTypes\AttendanceSessionTypeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAttendanceSessionTypes extends ListRecords
{
    protected static string $resource = AttendanceSessionTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
