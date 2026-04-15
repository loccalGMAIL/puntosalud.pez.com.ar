<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class SchedulerController extends Controller
{
    public function run(Request $request): JsonResponse
    {
        $token = config('app.scheduler_token');

        if (! $token || $request->bearerToken() !== $token) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        Artisan::call('whatsapp:send-reminders');
        $output = Artisan::output();

        return response()->json([
            'success' => true,
            'output'  => trim($output),
        ]);
    }
}
