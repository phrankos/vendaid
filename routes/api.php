<?php

use App\Http\Controllers\MainSystemController;
use App\Models\Medicine;
use App\Models\Patient;
use App\Models\Prescription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('scan', [MainSystemController::class, 'receive'])->name('QR.receive');
// Route::post('scan', [MainSystemController::class, 'receiveDebug'])->name('QR.receive'); //debug

// Test endpoint — bypasses MOSIP and camera, looks up real patient prescription.
// POST /api/test-dispense  body: { "uin": "7401478198" }
Route::post('test-dispense', function (Request $request) {
    $uin = $request->input('uin');
    if (!$uin) {
        return response()->json(['status' => 'error', 'message' => 'uin is required'], 400);
    }

    $patient = Patient::where('scan_id', $uin)->first();
    if (!$patient) {
        return response()->json(['status' => 'error', 'message' => 'Patient not found'], 404);
    }

    $prescription = Prescription::where('patient_id', $patient->id)
        ->where('expires_at', '>', now())
        ->orderBy('created_at', 'desc')
        ->first();

    if (!$prescription) {
        return response()->json(['status' => 'error', 'message' => 'No valid prescription'], 404);
    }

    $binaryString = $prescription->medicines_binary;
    $medicineBinaries = [];
    for ($i = 0; $i < strlen($binaryString); $i++) {
        if ($binaryString[$i] === '1') {
            $medicineBinaries[] = str_pad(decbin($i), 3, '0', STR_PAD_LEFT);
        }
    }

    return response()->json([
        'status'          => 'success',
        'message'         => 'Dispense Medicines',
        'medicine_binary' => $medicineBinaries,
    ]);
});