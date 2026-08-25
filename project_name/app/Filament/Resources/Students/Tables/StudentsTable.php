<?php

namespace App\Filament\Resources\Students\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use App\Models\SchoolClass;
use Carbon\Carbon;

class StudentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->searchable(),
                TextColumn::make('student_number')
                    ->searchable(),
                TextColumn::make('first_name')
                    ->searchable(),
                TextColumn::make('middle_name')
                    ->searchable(),
                TextColumn::make('last_name')
                    ->searchable(),
                TextColumn::make('gender')
                    ->searchable(),
                TextColumn::make('date_of_birth')
                    ->date()
                    ->sortable(),
                TextColumn::make('phone')
                    ->searchable(),
                TextColumn::make('guardian_name')
                    ->searchable(),
                TextColumn::make('guardian_phone')
                    ->searchable(),
                TextColumn::make('enrollment_status')
                    ->searchable(),
                TextColumn::make('joined_at')
                    ->date()
                    ->sortable(),
                TextColumn::make('left_at')
                    ->date()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                  Action::make('assignClass')
                    ->label('Assign Class')
                    ->icon('heroicon-o-academic-cap')
                    ->color('primary')
                    ->form([
                        Select::make('class_id')
                            ->label('Select Class')
                            ->options(SchoolClass::where('is_active', true)->pluck('name', 'id'))
                            ->searchable()
                            ->required(),
                        DatePicker::make('start_date')
                            ->label('Effective Start Date')
                            ->default(now())
                            ->required(),
                    ])
                    ->action(function ($record, array $data): void {
                        // This calls the safe transaction method we wrote in the Student model
                        $record->assignToClass($data['class_id'], Carbon::parse($data['start_date']));
                    })
                    ->successNotificationTitle('Student assigned to class successfully.'),
            ])

            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ;
    }
}
