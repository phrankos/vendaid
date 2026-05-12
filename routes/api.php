<?php

use App\Http\Controllers\MainSystemController;
use App\Models\Medicine;
use App\Models\Patient;
use App\Models\PendingTransaction;
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
    $allMedicines = Medicine::orderBy('id')->get();
    $medicineBinaries = [];
    $requiredMedicines = [];
    $missingMedicines = [];

    for ($i = 0; $i < strlen($binaryString); $i++) {
        if ($binaryString[$i] === '1' && isset($allMedicines[$i])) {
            $medicine = $allMedicines[$i];
            $medicineBinaries[] = str_pad(decbin($i + 1), 3, '0', STR_PAD_LEFT);
            $requiredMedicines[] = $medicine->id;
            if ($medicine->amount_left < 1) {
                $missingMedicines[] = [$medicine->id, $medicine->name];
            }
        }
    }

    if (!empty($missingMedicines)) {
        return response()->json([
            'status'           => 'error',
            'message'          => 'Cannot dispense all required medicines',
            'missing_medicines' => $missingMedicines,
        ]);
    }

    $pending = new PendingTransaction();
    $pending->fill([
        'scan_id'          => $uin,
        'transaction_hash' => hash('sha256', random_bytes(32)),
    ]);
    $pending->save();

    return response()->json([
        'status'           => 'success',
        'message'          => 'Dispense Medicines',
        'medicine_binary'  => $medicineBinaries,
        'medicines'        => $requiredMedicines,
        'transaction_hash' => $pending->transaction_hash,
    ]);
});


// Sensor test endpoint — bypasses everything, returns a hardcoded success so
// the scanner immediately runs the full dispense + sensor detection flow.
// Change medicine_binary to test different motor combinations.
// POST /api/test-sensor  body: anything (uin not required)
Route::post('test-sensor', function (Request $request) {
    return response()->json([
        'status'          => 'success',
        'message'         => 'Dispense Medicines',
        'medicine_binary' => ['001'],  // motor 1 — change as needed
    ]);
});

Route::post('dispensed', [MainSystemController::class, 'dispensed'])->name('dispense.success');
