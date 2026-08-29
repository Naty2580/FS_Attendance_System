<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\SchoolClass;
use App\Models\AttendanceSessionType;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;

class PdfExportController extends Controller
{
    public function __invoke(Request $request)
    {
        if (!auth()->user()->can('export attendance reports')) {
            abort(403, 'Unauthorized to export reports.');
        }

        // Get filters from query parameters
        $fromDate = $request->query('from_date', now()->subDays(30)->format('Y-m-d'));
        $toDate = $request->query('to_date', now()->format('Y-m-d'));
        $classId = $request->query('class_id');
        $sessionTypeId = $request->query('session_type_id');
        $countLate = filter_var($request->query('count_late', true), FILTER_VALIDATE_BOOLEAN);
        $countPermission = filter_var($request->query('count_permission', false), FILTER_VALIDATE_BOOLEAN);
        
        // 🌟 GET THE SEARCH STRING
        $search = $request->query('search');

        // Build the base query
        $query = Student::query()->where('enrollment_status', 'active');
        
        if ($classId) {
            $query->whereHas('classHistory', function (Builder $q) use ($classId) {
                $q->where('class_id', $classId)->where('is_current', true);
            });
        }

        // 🌟 APPLY THE EXACT SAME SEARCH LOGIC AS FILAMENT
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'ilike', "%{$search}%")
                  ->orWhere('last_name', 'ilike', "%{$search}%")
                  ->orWhere('student_number', 'ilike', "%{$search}%");
            });
        }

        $studentsRaw = $query->orderBy('first_name')->get();

        $students = $studentsRaw->map(function ($student) use ($fromDate, $toDate, $sessionTypeId, $countLate, $countPermission) {
            
            $countStatus = function ($statusCode) use ($student, $fromDate, $toDate, $sessionTypeId) {
                return $student->attendanceRecords()
                    ->whereHas('status', fn ($q) => $q->where('code', $statusCode))
                    ->whereHas('session', function ($q) use ($fromDate, $toDate, $sessionTypeId) {
                        $q->whereBetween('session_date', [$fromDate, $toDate]);
                        if ($sessionTypeId) {
                            $q->where('session_type_id', $sessionTypeId);
                        }
                    })->count();
            };

            $present = $countStatus('present');
            $late = $countStatus('late');
            $permission = $countStatus('permission');
            $absent = $countStatus('absent');

            $score = $present + ($countLate ? $late : 0) + ($countPermission ? $permission : 0);

            return [
                'student_number' => $student->student_number,
                'full_name' => $student->full_name,
                'present' => $present,
                'late' => $late,
                'permission' => $permission,
                'absent' => $absent,
                'score' => $score,
            ];
        });

        $filters = [
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'class_name' => $classId ? SchoolClass::find($classId)?->name : 'All Classes',
            'session_type_name' => $sessionTypeId ? AttendanceSessionType::find($sessionTypeId)?->name : 'All Types',
            'search_term' => $search, // Show what they searched for on the PDF!
        ];

        // Generate the PDF
        $pdf = Pdf::loadView('reports.attendance-pdf', [
            'students' => $students,
            'filters' => $filters
        ]);

        return $pdf->download('attendance_report_' . now()->format('Ymd') . '.pdf');
    }
}