<?php

use App\Http\Controllers\AnswerController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\TicketController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware(['auth:sanctum','throttle:api'])->group(function () {

    Route::patch('tickets/{id}/restore', [TicketController::class,'restore']);
    Route::put('tickets/{ticket}/assign', [TicketController::class,'assign']);
    Route::patch('tickets/{ticket}/resolve', [TicketController::class, 'resolve']);
    Route::patch('tickets/{ticket}/close', [TicketController::class, 'close']);
    Route::post('/tickets/{ticket}/addAgent', [TicketController::class, 'addAgent']);
    Route::apiResource('tickets', TicketController::class);

    Route::apiResource('tickets.answers', AnswerController::class)->only(['store', 'update','destroy']);
    Route::get('/dashboard/stats', [DashboardController::class, 'stats']);

    Route::get('/{file}/download', [FileController::class, 'download']);
    Route::apiResource('files', FileController::class)->only('destroy');
});
