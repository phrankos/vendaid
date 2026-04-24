<?php

use App\Http\Controllers\MainSystemController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('scan', [MainSystemController::class, 'receive'])->name('QR.receive');