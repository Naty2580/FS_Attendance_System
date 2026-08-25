<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Table;
use Filament\Tables;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;

// EXPLICIT IMPORTS - No aliases to prevent "Not Found" errors
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;

use App\Models\Student;
use App\Models\SchoolClass;
use App\Models\AttendanceSessionType;
use Illuminate\Database\Eloquent\Builder;

class AttendanceReport extends Page implements HasTable, HasForms
{
    use InteractsWithTable, InteractsWithForms;

    protected string $view = 'filament.pages.attendance-report';

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-document-chart-bar';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'HR Department';
    }

    // Filament v5 requires a single array property to hold form state
    public ?array $data = [];

    // Individual properties for the table query to read easily
    public ?string $from_date = null;
    public ?string $to_date = null;
    public ?string $class_id = null;
    public ?string $session_type_id = null;
    public bool $count_late = true;
    public bool $count_permission = false;

    public function mount(): void
    {
        // 1. Set individual properties
        $this->from_date = now()->subDays(30)->format('Y-m-d');
        $this->to_date = now()->format('Y-m-d');
        $this->count_late = true;
        $this->count_permission = false;

        // 2. DIRECTLY inject into the data array 
        // (This bypasses the missing $this->form property error!)
        $this->data = [
            'from_date' => $this->from_date,
            'to_date' => $this->to_date,
            'count_late' => $this->count_late,
            'count_permission' => $this->count_permission,
            'class_id' => null,
            'session_type_id' => null,
        ];
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Report Filters')
                    ->columns(3)
                    ->schema([
                        DatePicker::make('from_date')->label('From Date')->required(),
                        DatePicker::make('to_date')->label('To Date')->required(),
                        Select::make('class_id')
                            ->label('Filter by Class (Optional)')
                            ->options(SchoolClass::where('is_active', true)->pluck('name', 'id'))
                            ->searchable(),
                        Select::make('session_type_id')
                            ->label('Filter by Session Type (Optional)')
                            ->options(AttendanceSessionType::where('is_active', true)->pluck('name', 'id')),
                        
                        Toggle::make('count_late')
                            ->label('Count Late as Attendance (Weight 1)')
                            ->inline(false),
                        Toggle::make('count_permission')
                            ->label('Count Permission as Attendance (Weight 1)')
                            ->inline(false),
                    ])
            ])
            ->statePath('data'); 
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(function () {
                $query = Student::query()->where('enrollment_status', 'active');

                if ($this->class_id) {
                    $query->whereHas('classHistory', function (Builder $q) {
                        $q->where('class_id', $this->class_id)->where('is_current', true);
                    });
                }

                return $query;
            })
            ->columns([
                Tables\Columns\TextColumn::make('student_number')->label('ID')->sortable(),
                Tables\Columns\TextColumn::make('full_name')->label('Student Name')->sortable(),
                
                Tables\Columns\TextColumn::make('present_count')
                    ->label('Present')
                    ->badge()
                    ->color('success')
                    ->state(fn (Student $record) => $this->getAttendanceCount($record, 'present')),
                
                Tables\Columns\TextColumn::make('late_count')
                    ->label('Late')
                    ->badge()
                    ->color('warning')
                    ->state(fn (Student $record) => $this->getAttendanceCount($record, 'late')),

                Tables\Columns\TextColumn::make('permission_count')
                    ->label('Permission')
                    ->badge()
                    ->color('info')
                    ->state(fn (Student $record) => $this->getAttendanceCount($record, 'permission')),

                Tables\Columns\TextColumn::make('absent_count')
                    ->label('Absent')
                    ->badge()
                    ->color('danger')
                    ->state(fn (Student $record) => $this->getAttendanceCount($record, 'absent')),

                Tables\Columns\TextColumn::make('score')
                    ->label('Attendance Score')
                    ->weight('bold')
                    ->state(function (Student $record) {
                        $present = $this->getAttendanceCount($record, 'present');
                        $late = $this->count_late ? $this->getAttendanceCount($record, 'late') : 0;
                        $permission = $this->count_permission ? $this->getAttendanceCount($record, 'permission') : 0;
                        
                        return $present + $late + $permission;
                    }),
            ])
            ->headerActions([
                \Filament\Actions\ExportAction::make()
                    ->label('Export Excel')
                    ->exporter(\App\Filament\Exports\StudentExporter::class)
                    ->color('success'),
                
                \Filament\Actions\Action::make('export_pdf')
                    ->label('Export PDF')
                    ->color('danger')
                    ->icon('heroicon-o-document-arrow-down')
                    ->url(fn () => route('export.attendance.pdf', [
                        'from_date' => $this->from_date,
                        'to_date' => $this->to_date,
                        'class_id' => $this->class_id,
                        'session_type_id' => $this->session_type_id,
                        'count_late' => $this->count_late,
                        'count_permission' => $this->count_permission,
                    ]))
                    ->openUrlInNewTab(),
            ]);
    }

   private function getAttendanceCount(Student $student, string $statusCode): int
    {
        return $student->attendanceRecords()
            ->whereHas('status', fn ($q) => $q->where('code', $statusCode))
            // FIX: Changed from 'sessionClass.session' to just 'session'
            ->whereHas('session', function ($q) {
                $q->whereBetween('session_date', [$this->from_date, $this->to_date]);
                
                if ($this->session_type_id) {
                    $q->where('session_type_id', $this->session_type_id);
                }
            })
            ->count();
    }

    public function updated($propertyName)
    {
        // 3. Keep our local variables synced with the $data array when the user clicks a toggle
        if (str_starts_with($propertyName, 'data.')) {
            $key = str_replace('data.', '', $propertyName);
            if (property_exists($this, $key)) {
                $this->{$key} = $this->data[$key];
            }
        }
        $this->resetTable();
    }
}