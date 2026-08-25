<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use App\Models\Student;
use App\Models\AttendanceRecordChange;
use App\Observers\StudentObserver;
use App\Observers\AttendanceRecordChangeObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);
        Student::observe(StudentObserver::class);
        AttendanceRecordChange::observe(AttendanceRecordChangeObserver::class);
    }
}
