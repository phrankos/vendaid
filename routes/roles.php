<?php

use App\Http\Controllers\MedicineController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\PrescriptionController;
use App\Http\Controllers\RecordController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Admin\StudentTypeController;
use App\Http\Controllers\Admin\LatinHonorController;
use App\Http\Controllers\Admin\NameSuffixController;
use App\Http\Controllers\Admin\SexController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// User
// Route::middleware(['auth', 'role:2'])->group(function () {
//     // User Record
//     Route::put('user/information', [RecordController::class, 'userUpdate']);

//     //User Affiliations
//     Route::put('user/affiliations', [AffiliationController::class, 'userUpdate']);
//     Route::post('user/affiliations', [AffiliationController::class, 'store']);
//     Route::delete('user/affiliations/{id}', [AffiliationController::class, 'destroy'])
//         ->name('user.affiliations.destroy');
// });

// Doctor
Route::middleware(['auth', 'role:1'])->group(function () {
    // Patients
    Route::put('patients', [PatientController::class, 'update']);
    Route::post('patients', [PatientController::class, 'store']);
    Route::delete('patients/{id}', [PatientController::class, 'destroy'])
        ->name('patients.destroy');
    
    // Users
    // Route::put('admin/users', [UserController::class, 'update']);
    // Route::post('admin/users', [UserController::class, 'store']);
    // Route::delete('admin/users/{id}', [UserController::class, 'destroy'])
    //     ->name('admin.users.destroy');

    // Medicines
    Route::put('medicines', [MedicineController::class, 'update']);
    Route::post('medicines', [MedicineController::class, 'store']);
    Route::delete('medicines/{id}', [MedicineController::class, 'destroy'])
        ->name('medicines.destroy');

    // Prescriptions
    Route::post('prescriptions', [PrescriptionController::class, 'store'])->name('prescriptions.store');
    Route::put('prescriptions', [PrescriptionController::class, 'update'])->name('prescriptions.update');
    Route::delete('prescriptions/{id}', [PrescriptionController::class, 'destroy'])->name('prescriptions.destroy');

    // Name Suffixes
    // Route::post('admin/suffix', [NameSuffixController::class, 'store'])->name('admin.suffix.store');
    // Route::put('admin/suffix/{id}', [NameSuffixController::class, 'update'])->name('admin.suffix.update');
    // Route::delete('admin/suffix/{id}', [NameSuffixController::class, 'destroy'])->name('admin.suffix.destroy');

    // Sexes
    // Route::post('admin/sex', [SexController::class, 'store'])->name('admin.sex.store');
    // Route::put('admin/sex/{id}', [SexController::class, 'update'])->name('admin.sex.update');
    // Route::delete('admin/sex/{id}', [SexController::class, 'destroy'])->name('admin.sex.destroy');

    // Student Types
    // Route::post('admin/student-type', [StudentTypeController::class, 'store'])->name('admin.student-type.store');
    // Route::put('admin/student-type/{id}', [StudentTypeController::class, 'update'])->name('admin.student-type.update');
    // Route::delete('admin/student-type/{id}', [StudentTypeController::class, 'destroy'])->name('admin.student-type.destroy');
});