<?php

namespace App\Filament\Resources\AttendanceSessions\RelationManagers;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\SchoolClass;
use Illuminate\Validation\Rules\Unique;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;

class SessionClassesRelationManager extends RelationManager
{
    protected static string $relationship = 'sessionClasses';
    
    protected static ?string $title = 'Assigned Classes';

    // In v5, we use Schema instead of Form
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('class_id')
                    ->label('Select Class')
                    ->options(SchoolClass::where('is_active', true)->pluck('name', 'id'))
                    ->searchable()
                    ->required()
                    ->unique(
                        table: 'attendance_session_classes',
                        column: 'class_id',
                        modifyRuleUsing: fn (Unique $rule, $livewire) => $rule->where('attendance_session_id', $livewire->ownerRecord->id)
                    ),
                Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'ongoing' => 'Ongoing',
                        'completed' => 'Completed',
                    ])
                    ->default('pending')
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('schoolClass.name')
                    ->label('Class Name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('schoolClass.classLevel.name')
                    ->label('Level')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'ongoing',
                        'primary' => 'completed',
                    ]),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Add Class to Session')
                    ->modalHeading('Add Class to Session'),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make()
                    ->label('Remove'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}