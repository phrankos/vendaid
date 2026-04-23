<?php

use App\Http\Controllers\MedicineController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\PrescriptionController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Doctor
Route::middleware(['auth', 'role:1'])->group(function () {
    // Patients
    Route::put('patients', [PatientController::class, 'update']);
    Route::post('patients', [PatientController::class, 'store']);
    Route::delete('patients/{id}', [PatientController::class, 'destroy'])
        ->name('patients.destroy');

    // Medicines
    Route::put('medicines', [MedicineController::class, 'update']);
    Route::post('medicines', [MedicineController::class, 'store']);
    Route::delete('medicines/{id}', [MedicineController::class, 'destroy'])
        ->name('medicines.destroy');

    // Prescriptions
    Route::post('prescriptions', [PrescriptionController::class, 'store'])->name('prescriptions.store');
    Route::put('prescriptions', [PrescriptionController::class, 'update'])->name('prescriptions.update');
    Route::delete('prescriptions/{id}', [PrescriptionController::class, 'destroy'])->name('prescriptions.destroy');

});