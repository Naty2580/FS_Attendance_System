<?php

namespace App\Filament\Resources\AttendanceSchedules\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AttendanceScheduleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('General Information')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g. Saturday 4PM Course'),
                            
                        Select::make('session_type_id')
                            ->relationship('sessionType', 'name')
                            ->required(),
                            
                        Select::make('day_of_week')
                            ->options([
                                'Monday' => 'Monday',
                                'Tuesday' => 'Tuesday',
                                'Wednesday' => 'Wednesday',
                                'Thursday' => 'Thursday',
                                'Friday' => 'Friday',
                                'Saturday' => 'Saturday',
                                'Sunday' => 'Sunday',
                            ])
                            ->required(),
                            
                        Select::make('classes')
                            ->relationship('classes', 'name')
                            ->multiple()
                            ->preload()
                            ->label('Classes Assigned to this Schedule')
                            ->required(),
                    ]),

                Section::make('Time & Window Configuration')
                    ->description('Determine when teachers can start the session, and how long the attendance windows last AFTER they click start.')
                    ->columns(2)
                    ->schema([
                        TimePicker::make('expected_start_time')
                            ->label('Expected Start Time (Optional)')
                            ->helperText('The time the class usually begins.'),
                            
                        TextInput::make('start_window_before_minutes')
                            ->numeric()
                            ->label('Early Start Allowance (Minutes)')
                            ->default(30)
                            ->helperText('How many minutes BEFORE the expected time can they click Start?'),
                            
                        TextInput::make('start_window_after_minutes')
                            ->numeric()
                            ->label('Late Start Allowance (Minutes)')
                            ->default(60)
                            ->helperText('How many minutes AFTER the expected time can they still click Start?'),
                            
                        TextInput::make('present_grace_minutes')
                            ->numeric()
                            ->label('Present Window (Minutes)')
                            ->default(15)
                            ->required()
                            ->helperText('How long do they have to mark students as "Present" AFTER clicking Start?'),
                            
                        TextInput::make('late_grace_minutes')
                            ->numeric()
                            ->label('Late Window (Minutes)')
                            ->default(15)
                            ->required()
                            ->helperText('How long does the "Late" window last AFTER the Present window closes?'),
                            
                        TextInput::make('total_session_minutes')
                            ->numeric()
                            ->label('Total Session Duration (Minutes)')
                            ->default(60)
                            ->required()
                            ->helperText('The system will auto-close the session and mark missing students as Absent after this many minutes from the actual start time.'),
                    ]),

                Section::make('Activation')
                    ->columns(2)
                    ->schema([
                        DatePicker::make('effective_from')->default(now())->required(),
                        DatePicker::make('effective_until')->helperText('Leave empty to run forever.'),
                        Toggle::make('is_active')->default(true)->inline(false),
                    ]),
            ]);
    }
}