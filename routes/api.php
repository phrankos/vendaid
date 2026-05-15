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

Route::get('status', [MainSystemController::class, 'status'])->name('system.status');
Route::post('scan', [MainSystemController::class, 'receive'])->name('QR.receive');
// Route::post('scan', [MainSystemController::class, 'receiveDebug'])->name('QR.receive'); //debug

// Test endpoint — uses mock server for identity verification, bypasses camera.
// POST /api/test-dispense  body: { "uin": "3276815024" }
// Optionally pass "name" to override the name stored in the DB.
Route::post('test-dispense', function (Request $request) {
    set_time_limit(0);

    $uin = $request->input('uin');
    if (!$uin) {
        return response()->json(['status' => 'error', 'message' => 'uin is required'], 400);
    }

    $patient = Patient::where('scan_id', $uin)->first();
    if (!$patient) {
        return response()->json(['status' => 'error', 'message' => 'Patient not found'], 404);
    }

    // Build full name: QR/request body takes priority over DB record.
    $dbName = trim(implode(' ', array_filter([
        $patient->first_name, $patient->middle_name, $patient->last_name,
    ])));
    $name = $request->input('name') ?: ($dbName ?: null);

    if (!$name) {
        return response()->json([
            'status'  => 'error',
            'message' => 'Patient name not on record — pass "name" in the request body',
        ], 400);
    }

    // ── Identity verification via mock server (through the Python service) ────
    // QR/request body always takes priority; fall back to the DB record.
    $dbDob = $patient->birthdate
        ? date('Y-m-d', strtotime((string) $patient->birthdate))
        : null;
    $dob = $request->input('dob') ?: $dbDob;

    $payload = ['uin' => $uin, 'name' => $name];
    if ($dob) $payload['dob'] = $dob;

    // Forward any extra demographic fields the QR includes — the mock server
    // accepts all of them for verification.
    foreach (['gender', 'age', 'phone_number', 'email_id', 'postal_code',
              'location1', 'location3', 'zone',
              'address_line1', 'address_line2', 'address_line3'] as $field) {
        if ($request->has($field)) $payload[$field] = $request->input($field);
    }

    $ctx = stream_context_create(['http' => [
        'method'  => 'POST',
        'header'  => "Content-Type: application/json\r\n",
        'content' => json_encode($payload),
        'timeout' => 60,
    ]]);

    $kyc = null;
    try {
        $stream = fopen('http://127.0.0.1:5000/verify', 'r', false, $ctx);
        if ($stream === false) {
            throw new \RuntimeException('Cannot connect to verify service');
        }
        while (($line = fgets($stream)) !== false) {
            $line = trim($line);
            if ($line === '') continue;
            $parsed = json_decode($line, true);
            // Skip "waiting" heartbeat lines; take the first final result.
            if (($parsed['status'] ?? '') !== 'waiting') {
                $kyc = $parsed;
                break;
            }
        }
        fclose($stream);
    } catch (\Exception $e) {
        return response()->json([
            'status'  => 'error',
            'message' => 'Identity verification service unavailable: ' . $e->getMessage(),
        ], 503);
    }

    if ($kyc === null || !($kyc['verified'] ?? false)) {
        return response()->json([
            'status'         => 'error',
            'message'        => 'Identity verification failed',
            'uin'            => $uin,
            'mosip_verified' => false,
            'errors'         => $kyc['errors'] ?? [],
        ], 200);
    }

    // ── Identity confirmed — proceed with dispensing logic ────────────────────

    $prescription = Prescription::where('patient_id', $patient->id)
        ->where('expires_at', '>', now())
        ->orderBy('created_at', 'desc')
        ->first();

    if (!$prescription) {
        return response()->json([
            'status'         => 'error',
            'message'        => 'No valid prescription',
            'uin'            => $uin,
            'mosip_verified' => true,
        ], 404);
    }

    $claimedThisMonth = $patient->claimed_at &&
        date('Y-m', strtotime($patient->claimed_at)) === date('Y-m');
    if ($claimedThisMonth) {
        return response()->json([
            'status'         => 'error',
            'message'        => 'Already claimed for this month',
            'uin'            => $uin,
            'mosip_verified' => true,
        ], 200);
    }

    $binaryString = $prescription->medicines_binary;
    $allMedicines = Medicine::orderBy('id')->get();
    $requiredMedicines = [];
    $missingMedicines = [];

    for ($i = 0; $i < strlen($binaryString); $i++) {
        if ($binaryString[$i] === '1' && isset($allMedicines[$i])) {
            $medicine = $allMedicines[$i];
            $requiredMedicines[] = $medicine->id;
            if ($medicine->amount_left < 1) {
                $missingMedicines[] = [$medicine->id, $medicine->name];
            }
        }
    }

    if (!empty($missingMedicines)) {
        return response()->json([
            'status'            => 'error',
            'message'           => 'Cannot dispense all required medicines',
            'uin'               => $uin,
            'mosip_verified'    => true,
            'missing_medicines' => $missingMedicines,
        ]);
    }

    $patient->claimed_at = now();
    $patient->save();

    $pending = new PendingTransaction();
    $pending->fill([
        'scan_id'          => $uin,
        'transaction_hash' => hash('sha256', random_bytes(32)),
    ]);
    $pending->save();

    return response()->json([
        'status'           => 'success',
        'message'          => 'Dispense Medicines',
        'uin'              => $uin,
        'mosip_verified'   => true,
        'medicines'        => $requiredMedicines,
        'transaction_hash' => $pending->transaction_hash,
    ]);
});


// Mock-server identity verification endpoint — no camera, no dispensing.
// Useful for confirming UIN + name are recognised by the mock server.
// POST /api/test-verify  body: { "uin": "3276815024", "name": "Ethan Kurt Espejo", "dob": "1954-09-11" }
// name and dob are optional if the patient exists in the DB.
Route::post('test-verify', function (Request $request) {
    set_time_limit(0);

    $uin = $request->input('uin');
    if (!$uin) {
        return response()->json(['status' => 'error', 'message' => 'uin is required'], 400);
    }

    // Prefer name/dob from request; fall back to patient record if it exists.
    $patient = Patient::where('scan_id', $uin)->first();

    $dbName = $patient ? trim(implode(' ', array_filter([
        $patient->first_name, $patient->middle_name, $patient->last_name,
    ]))) : '';
    $name = $request->input('name') ?: ($dbName ?: null);

    $dbDob = ($patient && $patient->birthdate)
        ? date('Y-m-d', strtotime((string) $patient->birthdate))
        : null;
    $dob = $request->input('dob') ?: $dbDob;

    if (!$name) {
        return response()->json([
            'status'  => 'error',
            'message' => 'name is required (not found in DB — pass it in the request body)',
        ], 400);
    }

    $payload = ['uin' => $uin, 'name' => $name];
    if ($dob) $payload['dob'] = $dob;

    // Forward any extra demographic fields the QR includes.
    foreach (['gender', 'age', 'phone_number', 'email_id', 'postal_code',
              'location1', 'location3', 'zone',
              'address_line1', 'address_line2', 'address_line3'] as $field) {
        if ($request->has($field)) $payload[$field] = $request->input($field);
    }

    $ctx = stream_context_create(['http' => [
        'method'  => 'POST',
        'header'  => "Content-Type: application/json\r\n",
        'content' => json_encode($payload),
        'timeout' => 60,
    ]]);

    $kyc = null;
    try {
        $stream = fopen('http://127.0.0.1:5000/verify', 'r', false, $ctx);
        if ($stream === false) {
            throw new \RuntimeException('Cannot connect to verify service');
        }
        while (($line = fgets($stream)) !== false) {
            $line = trim($line);
            if ($line === '') continue;
            $parsed = json_decode($line, true);
            if (($parsed['status'] ?? '') !== 'waiting') {
                $kyc = $parsed;
                break;
            }
        }
        fclose($stream);
    } catch (\Exception $e) {
        return response()->json([
            'status'  => 'error',
            'message' => 'Verify service unavailable: ' . $e->getMessage(),
        ], 503);
    }

    if ($kyc === null) {
        return response()->json([
            'status'  => 'error',
            'message' => 'No response from verify service',
        ], 503);
    }

    // Return the raw KYC result so the caller can inspect everything.
    return response()->json($kyc);
});


// Sensor test endpoint — bypasses everything, returns a hardcoded success so
// the scanner immediately runs the full dispense + sensor detection flow.
// Change medicine_binary to test different motor combinations.
// POST /api/test-sensor  body: anything (uin not required)
Route::post('test-sensor', function (Request $request) {
    return response()->json([
        'status'          => 'success',
        'message'         => 'Dispense Medicines',
        'medicine_motors' => [1],  // motor 1 — change as needed
    ]);
});

Route::post('dispensed', [MainSystemController::class, 'dispensed'])->name('dispense.success');

// PATCH /api/patients/{uin}/claimed-at
// body: { "claimed_at": "2026-05-01" } or { "claimed_at": null } to clear
Route::patch('patients/{uin}/claimed-at', function (Request $request, string $uin) {
    $patient = Patient::where('scan_id', $uin)->first();
    if (!$patient) {
        return response()->json(['status' => 'error', 'message' => 'Patient not found'], 404);
    }
    $patient->claimed_at = $request->input('claimed_at');
    $patient->save();
    return response()->json([
        'status'     => 'ok',
        'uin'        => $uin,
        'claimed_at' => $patient->claimed_at,
    ]);
});
