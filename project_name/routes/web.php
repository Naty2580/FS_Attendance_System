<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Attendance\SyncController;
use App\Http\Controllers\Mobile\DashboardController;
use App\Http\Controllers\Mobile\AttendanceRosterController; 
use App\Http\Controllers\Report\PdfExportController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
});

Route::middleware(['auth'])->group(function () {
    //test route
    Route::get('/test', function () {
        return Inertia::render('Welcome');
    })->name('test');
    Route::post('/sync/attendance', SyncController::class)->name('attendance.sync');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
     Route::post('/dashboard/start-session', [DashboardController::class, 'startOrJoinSession'])->name('dashboard.start');
    Route::post('/attendance/{sessionId}/end', [AttendanceRosterController::class, 'endSession'])->name('attendance.end');

    Route::post('/attendance/{sessionId}/bulk-present', [AttendanceRosterController::class, 'bulkMarkPresent'])->name('attendance.bulk-present');

    Route::get('/attendance/{sessionId}', [AttendanceRosterController::class, 'show'])->name('attendance.roster');
     Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    Route::get('/export/attendance-pdf', PdfExportController::class)->name('export.attendance.pdf');
});

require __DIR__.'/auth.php';
