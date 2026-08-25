<?php

namespace App\Filament\Resources\AttendanceSchedules\Tables;


use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AttendanceSchedulesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('created_at')->dateTime('M d, Y H:i')->sortable()->searchable(),
                TextColumn::make('name')->sortable()->searchable(),
                TextColumn::make('sessionType.name')->label('Type')->sortable()->searchable(),
                TextColumn::make('day_of_week')->sortable()->searchable(),
                TextColumn::make('expected_start_time')->time('H:i')->label('Expected Start'),
                TextColumn::make('present_grace_minutes')->label('Present Window')->suffix(' min'),
                TextColumn::make('total_session_minutes')->label('Total Length')->suffix(' min'),
                IconColumn::make('is_active')->boolean(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
