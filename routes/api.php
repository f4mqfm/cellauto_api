<?php

use App\Http\Controllers\Api\AccessLogController;
use App\Http\Controllers\Api\BoardSaveController;
use App\Http\Controllers\Api\BoardSaveGroupController;
use App\Http\Controllers\Api\ColorController;
use App\Http\Controllers\Api\ColorListController;
use App\Http\Controllers\Api\ListController;
use App\Http\Controllers\Api\StaffTaskEvaluationController;
use App\Http\Controllers\Api\TaskEvaluationController;
use App\Http\Controllers\Api\TaskSaveController;
use App\Http\Controllers\Api\TaskSaveGroupController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\WordController;
use App\Http\Controllers\Api\WordGenMessageController;
use App\Http\Controllers\Api\WordRelationController;
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
Route::post('/access-logs/visit', [AccessLogController::class, 'storeVisit']);

Route::middleware(['auth:sanctum', 'active-session'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/access-logs/me', [AccessLogController::class, 'myLogs']);
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Lists (a bejelentkezett user saját listái)
    Route::get('/lists', [ListController::class, 'index']);
    Route::get('/public-lists', [ListController::class, 'publicIndex']);
    Route::post('/lists', [ListController::class, 'store']);
    Route::get('/lists/{list}', [ListController::class, 'show']);
    Route::put('/lists/{list}', [ListController::class, 'update']);
    Route::delete('/lists/{list}', [ListController::class, 'destroy']);

    // Words (egy listán belüli szavak kezelése)
    Route::get('/lists/{list}/words', [WordController::class, 'index']);
    Route::post('/lists/{list}/words', [WordController::class, 'store']);
    Route::put('/lists/{list}/word-generations', [WordController::class, 'replaceGenerations']);
    Route::get('/lists/{list}/word-gen-messages', [WordGenMessageController::class, 'index']);
    Route::put('/lists/{list}/word-gen-messages', [WordGenMessageController::class, 'replace']);
    Route::put('/lists/{list}/words/{word}', [WordController::class, 'update']);
    Route::delete('/lists/{list}/words/{word}', [WordController::class, 'destroy']);

    // Word relations (GENn -> GENn+1)
    Route::get('/lists/{list}/word-relations', [WordRelationController::class, 'index']);
    Route::post('/lists/{list}/word-relations', [WordRelationController::class, 'store']);
    Route::put('/lists/{list}/word-relations/from/{fromWord}', [WordRelationController::class, 'replaceForFromWord']);
    Route::delete('/lists/{list}/word-relations/{relation}', [WordRelationController::class, 'destroy']);

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

    // Feladat mentések (task_save_groups + task_saves + task_evaluations)
    Route::apiResource('task-save-groups', TaskSaveGroupController::class);
    Route::apiResource('task-save-groups.saves', TaskSaveController::class);
    Route::get('/task-saves/{task_save}/evaluations', [TaskEvaluationController::class, 'index']);
    Route::post('/task-saves/{task_save}/evaluations', [TaskEvaluationController::class, 'store']);
    Route::put('/task-saves/{task_save}/evaluations/{task_evaluation}', [TaskEvaluationController::class, 'update']);
    Route::delete('/task-saves/{task_save}/evaluations/{task_evaluation}', [TaskEvaluationController::class, 'destroy']);
});

Route::middleware(['auth:sanctum', 'active-session', 'staff'])->group(function () {
    Route::get('/staff/task-evaluations', [StaffTaskEvaluationController::class, 'index']);
    Route::get('/staff/task-evaluations/{task_evaluation}', [StaffTaskEvaluationController::class, 'show']);
});

Route::middleware(['auth:sanctum', 'active-session', 'admin'])->group(function () {
    Route::get('/admin/users/online-status', [UserController::class, 'onlineStatus']);
    Route::get('/access-logs', [AccessLogController::class, 'index']);
    Route::post('/users', [UserController::class, 'store']);
    Route::post('/users/{id}/suspend', [UserController::class, 'suspend']);
    Route::post('/users/{id}/unsuspend', [UserController::class, 'unsuspend']);
    Route::put('/users/{id}', [UserController::class, 'update']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);
});
