<?php

use App\Http\Controllers\MainSystemController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('scan', [MainSystemController::class, 'receive'])->name('QR.receive');
// Route::post('scan', [MainSystemController::class, 'receiveDebug'])->name('QR.receive'); //debug

Route::post('dispensed', [MainSystemController::class, 'dispensed'])->name('dispense.success');
