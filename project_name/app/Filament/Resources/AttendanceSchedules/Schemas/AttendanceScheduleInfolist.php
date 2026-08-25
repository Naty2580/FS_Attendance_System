<?php

namespace App\Filament\Resources\AttendanceSchedules\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class AttendanceScheduleInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('id')
                    ->label('ID'),
                TextEntry::make('sessionType.name')
                    ->label('Session type'),
                TextEntry::make('name'),
                TextEntry::make('day_of_week'),
                TextEntry::make('start_time')
                    ->time(),
                TextEntry::make('present_until')
                    ->time(),
                TextEntry::make('close_at')
                    ->time(),
                IconEntry::make('is_active')
                    ->boolean(),
                TextEntry::make('effective_from')
                    ->date(),
                TextEntry::make('effective_until')
                    ->date()
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
