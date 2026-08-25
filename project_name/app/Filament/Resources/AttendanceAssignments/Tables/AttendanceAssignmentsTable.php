<?php

namespace App\Filament\Resources\AttendanceAssignments\Tables;


use Filament\Tables\Columns\IconColumn;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class AttendanceAssignmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Teacher')
                    ->sortable()
                    ->searchable(),
                    
                TextColumn::make('schoolClass.name')
                    ->label('Class')
                    ->sortable()
                    ->searchable(),
                    
                TextColumn::make('starts_at')
                    ->date()
                    ->sortable(),
                    
                TextColumn::make('ends_at')
                    ->date()
                    ->sortable(),
                    
                IconColumn::make('is_active')
                    ->boolean(),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Active Status')
                    ->default(true),
            ])
            ->actions([
                EditAction::make(),
            ]);
    }
}