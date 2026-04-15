<?php

use App\Http\Controllers\Api\BoardSaveController;
use App\Http\Controllers\Api\BoardSaveGroupController;
use App\Http\Controllers\Api\ColorController;
use App\Http\Controllers\Api\ColorListController;
use App\Http\Controllers\Api\ListController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\WordController;
use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/ping', function () {
    return response()->json([
        'ok' => true,
        'message' => 'API mukodik',
    ]);
});

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Lists (a bejelentkezett user saját listái)
    Route::get('/lists', [ListController::class, 'index']);
    Route::post('/lists', [ListController::class, 'store']);
    Route::get('/lists/{list}', [ListController::class, 'show']);
    Route::put('/lists/{list}', [ListController::class, 'update']);
    Route::delete('/lists/{list}', [ListController::class, 'destroy']);

    // Words (egy listán belüli szavak kezelése)
    Route::get('/lists/{list}/words', [WordController::class, 'index']);
    Route::post('/lists/{list}/words', [WordController::class, 'store']);
    Route::put('/lists/{list}/words/{word}', [WordController::class, 'update']);
    Route::delete('/lists/{list}/words/{word}', [WordController::class, 'destroy']);

    // Színes listák (color_lists + colors)
    Route::get('/color-lists', [ColorListController::class, 'index']);
    Route::post('/color-lists', [ColorListController::class, 'store']);
    Route::get('/color-lists/{color_list}', [ColorListController::class, 'show']);
    Route::put('/color-lists/{color_list}', [ColorListController::class, 'update']);
    Route::delete('/color-lists/{color_list}', [ColorListController::class, 'destroy']);

    Route::get('/color-lists/{color_list}/colors', [ColorController::class, 'index']);
    Route::post('/color-lists/{color_list}/colors', [ColorController::class, 'store']);
    Route::put('/color-lists/{color_list}/colors/{color}', [ColorController::class, 'update']);
    Route::delete('/color-lists/{color_list}/colors/{color}', [ColorController::class, 'destroy']);

    // Táblaállapot mentések (docs/api-board-saves.md)
    Route::apiResource('board-save-groups', BoardSaveGroupController::class);
    Route::apiResource('board-save-groups.saves', BoardSaveController::class);
});

Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::post('/users', [UserController::class, 'store']);
    Route::post('/users/{id}/suspend', [UserController::class, 'suspend']);
    Route::post('/users/{id}/unsuspend', [UserController::class, 'unsuspend']);
    Route::put('/users/{id}', [UserController::class, 'update']);
});
