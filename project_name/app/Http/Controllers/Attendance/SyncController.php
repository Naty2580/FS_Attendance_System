<?php

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Controller;
use App\Http\Requests\SyncAttendanceRequest;
use App\Services\AttendanceSyncService;
use Illuminate\Http\JsonResponse;

class SyncController extends Controller
{
    public function __invoke(SyncAttendanceRequest $request, AttendanceSyncService $syncService): JsonResponse
    {
        $results = $syncService->processQueue(
            $request->validated('records'),
            $request->user()->id
        );

        return response()->json([
            'message' => 'Sync processed',
            'data' => $results,
        ]);
    }
}