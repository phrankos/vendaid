<?php

use App\Http\Controllers\MainSystemController;
use App\Http\Controllers\PrescriptionController;
use App\Http\Controllers\MedicineController;
use App\Http\Controllers\PatientController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'role:1|2'])->group(function () {
    Route::get('medicines', [MedicineController::class, 'index'])
        ->middleware(['auth', 'verified'])->name('medicines.refiller');
});

Route::middleware(['auth', 'role:1'])->group(function () {
    Route::get('patients', [PatientController::class, 'index'])
        ->middleware(['auth', 'verified'])->name('patients');
    Route::get('prescriptions', [PrescriptionController::class, 'index'])
        ->middleware(['auth', 'verified'])->name('prescriptions');
});


require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
require __DIR__.'/roles.php';
