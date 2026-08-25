<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Traits\HasRoles;


// #[Fillable(['name', 'email', 'password'])]
// #[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
     use HasFactory, Notifiable, HasUlids, SoftDeletes, HasRoles;

     protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'status',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

     // Relationships based on the Source of Truth

    public function departmentMemberships(): HasMany
    {
        return $this->hasMany(DepartmentMembership::class);
    }

    public function attendanceAssignments(): HasMany
    {
        return $this->hasMany(AttendanceAssignment::class);
    }

    public function attendanceRecordsRecorded(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class, 'recorded_by');
    }

     public function canAccessPanel(Panel $panel): bool
    {
        // Only specific privileged roles can access the Filament backend.
        // Regular "Attendance Members" will use the Inertia React mobile app.
        return $this->hasAnyRole([
            'System Administrator',
            'HR Leader',
            'HR Assistant Leader',
            'HR Writer',
            'Student Manager'
        ]);
    }
}
