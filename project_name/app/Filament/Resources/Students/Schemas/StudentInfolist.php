<?php

namespace App\Filament\Resources\Students\Schemas;

use App\Models\Student;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class StudentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('id')
                    ->label('ID'),
                TextEntry::make('student_number'),
                TextEntry::make('first_name'),
                TextEntry::make('middle_name')
                    ->placeholder('-'),
                TextEntry::make('last_name'),
                TextEntry::make('gender')
                    ->placeholder('-'),
                TextEntry::make('date_of_birth')
                    ->date()
                    ->placeholder('-'),
                TextEntry::make('phone')
                    ->placeholder('-'),
                TextEntry::make('guardian_name')
                    ->placeholder('-'),
                TextEntry::make('guardian_phone')
                    ->placeholder('-'),
                TextEntry::make('enrollment_status'),
                TextEntry::make('joined_at')
                    ->date()
                    ->placeholder('-'),
                TextEntry::make('left_at')
                    ->date()
                    ->placeholder('-'),
                TextEntry::make('notes')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn (Student $record): bool => $record->trashed()),
            ]);
    }
}
