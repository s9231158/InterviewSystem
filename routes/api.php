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