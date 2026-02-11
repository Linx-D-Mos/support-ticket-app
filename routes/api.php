<?php

use App\Http\Controllers\AnswerController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/login', [AuthController::class, 'login']);
Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {


    Route::patch('tickets/{id}/restore', [TicketController::class, 'restore']);
    Route::put('tickets/{ticket}/assign', [TicketController::class, 'assign']);
    Route::patch('tickets/{ticket}/resolve', [TicketController::class, 'resolve']);
    Route::patch('tickets/{ticket}/close', [TicketController::class, 'close']);
    Route::post('/tickets/{ticket}/addAgent', [TicketController::class, 'addAgent']);
    Route::apiResource('tickets', TicketController::class);
    Route::apiResource('users', UserController::class);
    Route::apiResource('tickets.answers', AnswerController::class)->only(['store', 'update', 'destroy']);
    Route::get('/dashboard/stats', [DashboardController::class, 'stats']);

    //Archivos
    Route::get('/files/{file}/download', [FileController::class, 'download']);
    Route::apiResource('files', FileController::class)->only('destroy');

    //Notificaciones
    Route::get('/notifications', [NotificationController::class,'index']);
    Route::patch('/notifications/{id}/read', [NotificationController::class,'markAsRead']);
    Route::post('notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);
});
