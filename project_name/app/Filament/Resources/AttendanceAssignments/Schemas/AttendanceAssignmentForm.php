<?php

namespace App\Filament\Resources\AttendanceAssignments\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class AttendanceAssignmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Fix: Properly mapped to the 'user' relationship to save the ULID
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->label('Attendance Taker (User)')
                    ->searchable()
                    ->preload()
                    ->required(),

                // Fix: Properly mapped to the 'schoolClass' relationship to save the ULID
                Select::make('class_id')
                    ->relationship('schoolClass', 'name')
                    ->label('Assigned Class')
                    ->searchable()
                    ->preload()
                    ->required(),

                DatePicker::make('starts_at')
                    ->label('Start Date')
                    ->default(now())
                    ->required(),

                DatePicker::make('ends_at')
                    ->label('End Date (Optional)')
                    ->helperText('Leave empty if this is a permanent assignment.'),

                Toggle::make('is_active')
                    ->label('Is Active')
                    ->default(true)
                    ->inline(false)
                    ->required(),
            ]);
    }
}