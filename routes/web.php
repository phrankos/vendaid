<?php

use App\Http\Controllers\AffiliationController;
use App\Http\Controllers\PrescriptionController;
use App\Http\Controllers\RecordController;
use App\Http\Controllers\MedicineController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\Admin\StudentTypeController;
use App\Http\Controllers\UserController;
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
    Route::get('admin/dashboard', function () {
        return Inertia::render('AdminDashboard');})->name('admin.dashboard');
    Route::get('patients', [PatientController::class, 'index'])
        ->middleware(['auth', 'verified'])->name('patients');
    Route::get('prescriptions', [PrescriptionController::class, 'index'])
        ->middleware(['auth', 'verified'])->name('prescriptions');
    // Route::get('medicines', [MedicineController::class, 'index'])
    //     ->middleware(['auth', 'verified'])->name('medicines.doctor');

    // Route::get('admin/users', [UserController::class, 'index'])
    //     ->middleware(['auth', 'verified'])->name('medicines');

    // Use StudentTypeController@index for the misc page so all tables are passed
    // Route::get('admin/misc', [StudentTypeController::class, 'index'])->name('admin.misc');
});


// Route::middleware(['auth', 'role:2'])->group(function () {
//     Route::get('user/information', [RecordController::class, 'read'])
//         ->middleware(['auth', 'verified'])->name('user.information');
//     Route::get('user/affiliations', [AffiliationController::class, 'read'])
//         ->middleware(['auth', 'verified'])->name('user.affiliations');
// });


require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
require __DIR__.'/roles.php';
