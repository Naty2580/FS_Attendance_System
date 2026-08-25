<?php

namespace App\Observers;

use App\Models\Student;
use App\Models\AuditLog;
use Illuminate\Support\Str;

class StudentObserver
{
    public function created(Student $student): void
    {
        $this->logAction($student, 'created', null, $student->toArray());
    }

    public function updated(Student $student): void
    {
        // Only log if something actually changed
        if ($student->wasChanged()) {
            $this->logAction($student, 'updated', $student->getOriginal(), $student->getChanges());
        }
    }

    public function deleted(Student $student): void
    {
        $this->logAction($student, 'deleted', $student->toArray(), null);
    }

    public function restored(Student $student): void
    {
        $this->logAction($student, 'restored', null, $student->toArray());
    }

    private function logAction(Student $student, string $action, ?array $oldValues, ?array $newValues): void
    {
        AuditLog::create([
            'id' => (string) Str::ulid(),
            'user_id' => auth()->id(), // Works for web requests
            'action' => $action,
            'auditable_type' => Student::class,
            'auditable_id' => $student->id,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}