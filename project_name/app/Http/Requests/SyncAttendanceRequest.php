<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SyncAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // As discussed, Service layer handles class-assignment security
    }

    public function rules(): array
    {
        return [
            'records' => ['required', 'array', 'max:500'],
            'records.*.sync_id' => ['required', 'string'],
            // UPDATED FOREIGN KEY HERE:
            'records.*.attendance_session_id' => ['required', 'string', 'exists:attendance_sessions,id'],
            'records.*.student_id' => ['required', 'string', 'exists:students,id'],
            'records.*.status_code' => ['required', 'string', 'exists:attendance_statuses,code'],
            'records.*.recorded_at' => ['required', 'date'],
        ];
    }
}