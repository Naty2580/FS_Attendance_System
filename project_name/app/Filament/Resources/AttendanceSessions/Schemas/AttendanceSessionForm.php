<?php

namespace App\Filament\Resources\AttendanceSessions\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Schema;

class AttendanceSessionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('session_type_id')
                    ->relationship('sessionType', 'name')
                    ->required(),
                TextInput::make('attendance_schedule_id'),
                DatePicker::make('session_date')
                    ->required(),
                TimePicker::make('starts_at')
                    ->required(),
                TimePicker::make('present_until')
                    ->required(),
                TimePicker::make('closes_at')
                    ->required(),
                TextInput::make('status')
                    ->required()
                    ->default('scheduled'),
            ]);
    }
}
