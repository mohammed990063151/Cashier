<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\StockAlertService;
use Illuminate\Http\JsonResponse;

class StockAlertController extends Controller
{
    public function index(StockAlertService $stockAlerts): JsonResponse
    {
        return response()->json($stockAlerts->payload(25));
    }
}
