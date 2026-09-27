<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\CollectionScheduleService;
use Illuminate\Http\JsonResponse;

class CollectionAlertController extends Controller
{
    public function index(CollectionScheduleService $schedule): JsonResponse
    {
        return response()->json($schedule->reminderPayload());
    }
}
