<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\UserController;

Route::get('/ping', function () {
    return response()->json([
        'ok' => true,
        'message' => 'API mukodik'
    ]);
});

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});

Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::post('/users', [UserController::class, 'store']);
    Route::post('/users/{id}/suspend', [UserController::class, 'suspend']);
    Route::post('/users/{id}/unsuspend', [UserController::class, 'unsuspend']);
    Route::put('/users/{id}', [UserController::class, 'update']);
});
