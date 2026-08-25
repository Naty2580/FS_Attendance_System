<?php

namespace App\Filament\Resources\AttendanceSessions\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class AttendanceSessionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('id')
                    ->label('ID'),
                TextEntry::make('sessionType.name')
                    ->label('Session type'),
                TextEntry::make('attendance_schedule_id')
                    ->placeholder('-'),
                TextEntry::make('session_date')
                    ->date(),
                TextEntry::make('starts_at')
                    ->time(),
                TextEntry::make('present_until')
                    ->time(),
                TextEntry::make('closes_at')
                    ->time(),
                TextEntry::make('status'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
