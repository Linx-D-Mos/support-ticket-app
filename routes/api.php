<?php

use App\Http\Controllers\AnswerController;
use App\Http\Controllers\TicketController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware(['auth:sanctum'])->group(function () {
    
    Route::post('/tickets/{ticket}/addAgent', [TicketController::class, 'addAgent']);
    Route::apiResource('tickets', TicketController::class);
    Route::post('/tickets/{ticket}/answers', [AnswerController::class, 'store']);
});
