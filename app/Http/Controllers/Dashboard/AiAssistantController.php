<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\AiAssistant\AssistantEngine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AiAssistantController extends Controller
{
    public function __construct(protected AssistantEngine $engine)
    {
    }

    public function chat(Request $request): JsonResponse
    {
        set_time_limit(60);

        $validated = $request->validate([
            'message' => 'nullable|string|max:1000',
            'action' => 'nullable|string|max:50',
            'product_id' => 'nullable|integer|exists:products,id',
            'intent' => 'nullable|string|max:50',
            'qty' => 'nullable|numeric|min:0',
            'unit' => 'nullable|string|max:30',
            'text' => 'nullable|string|max:1000',
        ]);

        $message = (string) ($validated['message'] ?? $validated['text'] ?? '');

        $result = $this->engine->handle($message, $validated);

        return response()->json([
            'ok' => true,
            'data' => $result,
        ]);
    }

    public function bootstrap(): JsonResponse
    {
        $result = $this->engine->handle('مرحبا');

        return response()->json([
            'ok' => true,
            'data' => $result,
        ]);
    }
}
