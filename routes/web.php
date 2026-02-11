<?php

use App\Http\Controllers\SignedStorageController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Route::get('signed/download/{path}', SignedStorageController::class)
//     ->where('path', '.*')
//     ->name('files.download')
//     ->middleware('signed');
// Route::view('/test-ws', 'test-ws');
// // Solo para probar rápido en web.php
// Route::get('/login-force', function () {
//     \Illuminate\Support\Facades\Auth::loginUsingId(1);
//     return redirect('/test-ws');
// });