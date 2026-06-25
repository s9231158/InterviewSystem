<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
Route::get('/test', function () {
    $logPath = storage_path('logs/deploy.log');
    $logContent = file_exists($logPath) ? file_get_contents($logPath) : 'No deploy log found.';
    
    $user = null;
    try {
        $user = App\Models\User::first();
    } catch (\Exception $e) {
        $user = 'Error: ' . $e->getMessage();
    }
    
    return response()->json([
        'deploy_log' => $logContent,
        'user' => $user,
    ]);
});

Route::middleware(\App\Http\Middleware\VerifyWorkerSignature::class)->group(function () {
    Route::get('/projects/{name}', [\App\Http\Controllers\ProjectController::class, 'show']);
    Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'index']);
    Route::post('/appointments', [\App\Http\Controllers\AppointmentController::class, 'store']);
    Route::post('/gemini-proxy', function (Request $request) {
        $model = $request->input('model', 'gemini-3.1-flash-lite');
        $payload = $request->except(['model']);
        $key = $request->header('X-Gemini-Key') ?: config('app.gemini_key');

        if (empty($key)) {
            return response()->json(['error' => 'Gemini API key is required.'], 400);
        }

        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$key}";

        try {
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post($url, $payload);

            return response($response->body(), $response->status())
                ->header('Content-Type', 'application/json');
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to proxy request to Gemini API.',
                'message' => $e->getMessage()
            ], 500);
        }
    });
});