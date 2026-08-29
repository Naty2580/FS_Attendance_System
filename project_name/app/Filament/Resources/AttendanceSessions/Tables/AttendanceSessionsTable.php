<?php

namespace App\Filament\Resources\AttendanceSessions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AttendanceSessionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->searchable(),
                TextColumn::make('sessionType.name')
                    ->searchable(),
                TextColumn::make('attendance_schedule_id')
                    ->searchable(),
                TextColumn::make('session_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('starts_at')
                    ->time()
                    ->sortable(),
                TextColumn::make('present_until')
                    ->time()
                    ->sortable(),
                TextColumn::make('closes_at')
                    ->time()
                    ->sortable(),
                TextColumn::make('status')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                 Action::make('reopen_session')
                    ->label('Reopen Session')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Reopen this Session?')
                    ->modalDescription('This will change the status back to Active and extend the allowed time window by 15 minutes, allowing teachers to fix mistakes on their phones.')
                    ->modalSubmitActionLabel('Yes, Reopen it')
                    // Only show this button if the session is actually closed
                    ->visible(fn (\App\Models\AttendanceSession $record) => $record->status === 'closed')
                    ->action(function (\App\Models\AttendanceSession $record) {
                        
                        // 1. Calculate how much time we need to add to trick the State Machine
                        // We want to give them exactly 15 more minutes of the "Late" window from RIGHT NOW.
                        
                        $now = Carbon::now();
                        $schedule = $record->schedule;
                        
                        // Total allowed time normally = present_grace + late_grace
                        $totalGraceMinutes = $schedule->present_grace_minutes + $schedule->late_grace_minutes;
                        
                        // We shift the 'started_at' time forward so that (started_at + totalGrace) = (now + 15 mins)
                        $newStartedAt = $now->copy()->addMinutes(15)->subMinutes($totalGraceMinutes);

                        // 2. Update the record
                        $record->update([
                            'status' => 'active',
                            'started_at' => $newStartedAt
                        ]);

                        // 3. Show a success toast notification to the HR Admin
                        Notification::make()
                            ->title('Session Reopened')
                            ->body('The session is active again. The teacher has 15 minutes to make changes.')
                            ->success()
                            ->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
