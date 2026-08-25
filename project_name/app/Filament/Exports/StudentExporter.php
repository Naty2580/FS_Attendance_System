<?php

namespace App\Filament\Exports;

use App\Models\Student;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class StudentExporter extends Exporter
{
    protected static ?string $model = Student::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('student_number')->label('Student ID'),
            ExportColumn::make('first_name')->label('First Name'),
            ExportColumn::make('last_name')->label('Last Name'),
            // We use state() callbacks to trigger the exact same query logic we used in the UI
            ExportColumn::make('present')->state(fn (Student $record) => $record->attendanceRecords()->whereHas('status', fn ($q) => $q->where('code', 'present'))->count()),
            ExportColumn::make('late')->state(fn (Student $record) => $record->attendanceRecords()->whereHas('status', fn ($q) => $q->where('code', 'late'))->count()),
            ExportColumn::make('permission')->state(fn (Student $record) => $record->attendanceRecords()->whereHas('status', fn ($q) => $q->where('code', 'permission'))->count()),
            ExportColumn::make('absent')->state(fn (Student $record) => $record->attendanceRecords()->whereHas('status', fn ($q) => $q->where('code', 'absent'))->count()),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your attendance report has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}