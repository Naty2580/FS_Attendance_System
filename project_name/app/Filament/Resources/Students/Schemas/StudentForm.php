<?php

namespace App\Filament\Resources\Students\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class StudentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('student_number')
                    ->required(),
                TextInput::make('first_name')
                    ->required(),
                TextInput::make('middle_name'),
                TextInput::make('last_name')
                    ->required(),
                TextInput::make('gender'),
                DatePicker::make('date_of_birth'),
                TextInput::make('phone')
                    ->tel(),
                TextInput::make('guardian_name'),
                TextInput::make('guardian_phone')
                    ->tel(),
                TextInput::make('enrollment_status')
                    ->required()
                    ->default('active'),
                DatePicker::make('joined_at'),
                DatePicker::make('left_at'),
                Textarea::make('notes')
                    ->columnSpanFull(),
            ]);
    }
}
