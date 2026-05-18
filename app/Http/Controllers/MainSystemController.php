<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use App\Models\Patient;
use App\Models\PendingTransaction;
use App\Models\Prescription;
use App\Models\Transaction;
use Carbon\Carbon;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class MainSystemController extends Controller
{
    // private const MOSIP_SERVICE_URL = 'http://127.0.0.1:5000';
    // private const ESP32_CAM_URL = 'http://172.20.10.2/capture_b64';
    // private const ESP32_CAM_URL = 'http://172.20.10.4/capture_b64';

    // private const ESP32_CAM_URL = 'http://172.20.10.4/capture_b64';
    // private const ESP32_CAM_URL = 'http://10.107.43.21/capture_b64';

    private const MOSIP_SERVICE_URL = 'http://127.0.0.1:5000';
    private const ESP32_CAM_URL = 'http://192.168.60.108/capture_b64';
    private const CAM_PRE_CAPTURE_DELAY_MS = 1500;
    private const SENIOR_AGE = 60;
    private const ALLOWED_BARANGAYS = ['U.P. Campus'];
    // private const ALLOWED_BARANGAYS = [];
    private const GENDER_TO_SEX_ID = ['Male' => 1, 'Female' => 2];

    public function status(): \Illuminate\Http\JsonResponse
    {
        $cameraOk = $this->fetchCameraImage() !== null;

        $pythonOk = false;
        try {
            $resp = Http::timeout(5)->get(self::MOSIP_SERVICE_URL . '/health');
            $pythonOk = $resp->ok();
        } catch (\Exception $e) {
            // Python service unreachable
        }

        $ready = $cameraOk && $pythonOk;
        return response()->json([
            'status' => $ready ? 'ready' : 'not_ready',
            'camera' => $cameraOk ? 'ok' : 'error',
            'python' => $pythonOk ? 'ok' : 'error',
        ], $ready ? 200 : 503);
    }

    public function receive(Request $request)
    {
        set_time_limit(0); // no limit — MOSIP retries can take up to ~130s
        $data = json_decode($request->getContent(), true);

        if (!is_array($data) || !isset($data['uin'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Request body must be JSON with a uin field',
            ], 400);
        }

        // Stream headers immediately so the scanner can show flash-countdown
        // messages while the camera is capturing (instead of blocking until
        // the full camera + MOSIP round-trip completes).
        return response()->stream(
            function () use ($data) {
                $this->handleReceiveStream($data);
            },
            200,
            ['Content-Type' => 'application/x-ndjson', 'X-Accel-Buffering' => 'no']
        );
    }

    private function handleReceiveStream(array $data): void
    {
        $uin       = (string) $data['uin'];
        $name      = $data['name'] ?? null;
        $dob       = $data['dob'] ?? null;
        $birthdate = $this->parseDob($dob);

        if ($dob && !$birthdate) {
            echo json_encode([
                'status'   => 'error',
                'message'  => 'Invalid date of birth',
                'eligible' => false,
                'reason'   => 'invalid_dob',
            ]) . "\n";
            return;
        }

        if ($birthdate) {
            $age = (int) $birthdate->diffInYears(now());
            if ($age < self::SENIOR_AGE) {
                echo json_encode([
                    'status'   => 'error',
                    'message'  => 'Patient is not eligible (under ' . self::SENIOR_AGE . ')',
                    'uin'      => $uin,
                    'eligible' => false,
                    'reason'   => 'underage',
                    'age'      => $age,
                ]) . "\n";
                return;
            }
        }

        $zone = $data['zone'] ?? null;
        if (!empty(self::ALLOWED_BARANGAYS) && $zone !== null && !in_array($zone, self::ALLOWED_BARANGAYS, true)) {
            echo json_encode([
                'status'   => 'error',
                'message'  => 'Patient is not in an eligible barangay',
                'uin'      => $uin,
                'eligible' => false,
                'reason'   => 'wrong_barangay',
                'barangay' => $zone,
            ]) . "\n";
            return;
        }

        if (empty($data['image_base64'])) {
            // Brief pause so the user can look at the camera, then signal the
            // scanner right before triggering the camera — this is the sync point
            // that lets the LCD flash messages align with the camera flashes.
            usleep(self::CAM_PRE_CAPTURE_DELAY_MS * 1000);
            echo json_encode(['status' => 'capturing']) . "\n";
            ob_flush();
            flush();

            $img = $this->fetchCameraImage();
            if ($img === null) {
                echo json_encode([
                    'status'  => 'error',
                    'message' => 'Camera capture failed',
                    'reason'  => 'camera_unreachable',
                ]) . "\n";
                ob_flush();
                flush();
                // Emit the error but do not return — KYC still runs without face matching.
            } else {
                // Camera just did its confirmation flash — tell the scanner
                echo json_encode(['status' => 'photo_taken']) . "\n";
                ob_flush();
                flush();

                $data['image_base64'] = $img;
            }
        }

        $payload = ['uin' => $uin, 'name' => $name];
        if (!empty($data['image_base64'])) $payload['image_base64'] = $data['image_base64'];
        if ($dob) $payload['dob'] = $dob;
        foreach (['gender', 'age', 'phone_number', 'email_id', 'postal_code',
                  'location1', 'location3', 'zone',
                  'address_line1', 'address_line2', 'address_line3'] as $field) {
            if (!empty($data[$field])) $payload[$field] = $data[$field];
        }
        $this->streamMosipScan($uin, $name, $data, $payload, $birthdate);
    }

    private function fetchCameraImage(): ?string
    {
        // Use PHP stream wrapper with HTTP/1.0 so the ESP32-CAM's malformed
        // chunked-encoding responses don't cause cURL error 56. HTTP/1.0 has
        // no chunked encoding — the server sends the body and closes the socket.
        $ctx = stream_context_create([
            'http' => [
                'method'           => 'GET',
                'timeout'          => 15,
                'protocol_version' => 1.0,
                'header'           => "Connection: close\r\n",
                'ignore_errors'    => true,
            ],
        ]);

        $body = @file_get_contents(self::ESP32_CAM_URL, false, $ctx);

        if ($body === false) {
            Log::warning('cam fetch failed for ' . self::ESP32_CAM_URL);
            return null;
        }

        $body = trim($body);
        if ($body === '') {
            Log::warning('cam returned empty body');
            return null;
        }

        return $body;
    }

    private function handleMosipScan(array $data)
    {
        $uin  = (string) $data['uin'];
        $name = $data['name'] ?? null;
        $dob  = $data['dob'] ?? null;

        $birthdate = $this->parseDob($dob);
        if ($dob && !$birthdate) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid date of birth',
                'eligible' => false,
                'reason' => 'invalid_dob',
            ], 400);
        }

        // if ($birthdate) {
        //     $age = (int) $birthdate->diffInYears(now());
        //     if ($age < self::SENIOR_AGE) {
        //         return response()->json([
        //             'status' => 'error',
        //             'message' => 'Patient is not eligible (under '.self::SENIOR_AGE.')',
        //             'uin' => $uin,
        //             'eligible' => false,
        //             'reason' => 'underage',
        //             'age' => $age,
        //         ], 200);
        //     }
        // }

        $payload = ['uin' => $uin, 'name' => $name];
        if (!empty($data['image_base64'])) {
            $payload['image_base64'] = $data['image_base64'];
        }

        return response()->stream(
            function () use ($uin, $name, $data, $payload, $birthdate) {
                $this->streamMosipScan($uin, $name, $data, $payload, $birthdate);
            },
            200,
            ['Content-Type' => 'application/x-ndjson', 'X-Accel-Buffering' => 'no']
        );
    }

    private function streamMosipScan(
        string $uin, ?string $name, array $data, array $payload, $birthdate
    ): void {
        // ── Call Python, forward waiting lines to Arduino immediately ──────────
        $ctx = stream_context_create(['http' => [
            'method'  => 'POST',
            'header'  => "Content-Type: application/json\r\n",
            'content' => json_encode($payload),
            'timeout' => 200,
        ]]);

        $kyc = null;
        try {
            $stream = fopen(self::MOSIP_SERVICE_URL.'/verify', 'r', false, $ctx);
            if ($stream === false) {
                throw new \RuntimeException('Could not open MOSIP stream');
            }
            while (($line = fgets($stream)) !== false) {
                $line = trim($line);
                if ($line === '') continue;
                $parsed = json_decode($line, true);
                if (($parsed['status'] ?? '') === 'waiting') {
                    echo json_encode($parsed) . "\n";
                    ob_flush(); flush();
                } else {
                    $kyc = $parsed ?? [];
                    break;
                }
            }
            fclose($stream);
        } catch (\Exception $e) {
            echo json_encode([
                'status' => 'error',
                'message' => 'MOSIP service unavailable',
                'mosip_verified' => false,
            ]) . "\n";
            return;
        }

        if ($kyc === null) {
            echo json_encode([
                'status' => 'error',
                'message' => 'MOSIP service unavailable',
                'mosip_verified' => false,
            ]) . "\n";
            return;
        }

        // ── Process KYC result ────────────────────────────────────────────────
        if (!($kyc['verified'] ?? false)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'MOSIP identity verification failed',
                'uin' => $uin,
                'eligible' => false,
                'mosip_verified' => false,
                'errors' => $kyc['errors'] ?? [],
            ]) . "\n";
            return;
        }

        $faceMatch = $kyc['face_match'] ?? null;
        if ($faceMatch !== null && !($faceMatch['verified'] ?? false)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Face does not match MOSIP record',
                'uin' => $uin,
                'eligible' => false,
                'mosip_verified' => true,
                'face_match' => $faceMatch,
            ]) . "\n";
            return;
        }

        // barangay restriction
        $barangay = $kyc['kyc_data']['zone_eng'] ?? null;
        if (!empty(self::ALLOWED_BARANGAYS)
            && (!$barangay || !in_array($barangay, self::ALLOWED_BARANGAYS, true))) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Patient is not in an eligible barangay',
                'uin' => $uin,
                'eligible' => false,
                'reason' => 'wrong_barangay',
                'barangay' => $barangay,
            ]) . "\n";
            return;
        }

        $sexId = self::GENDER_TO_SEX_ID[$kyc['kyc_data']['gender_eng'] ?? ''] ?? null;

        [$first, $middle, $last] = $this->splitName($name);
        $patient = Patient::updateOrCreate(
            ['scan_id' => $uin],
            [
                'first_name' => $first,
                'middle_name' => $middle,
                'last_name' => $last,
                'birthdate' => $birthdate,
                'barangay' => $barangay,
                'sex_id' => $sexId,
            ],
        );

        $prescription = Prescription::where('patient_id', $patient->id)
            ->where('expires_at', '>', now())
            ->orderBy('created_at', 'desc')
            ->first();

        if ($prescription) {
            $claimedThisMonth = $patient->claimed_at &&
                date('Y-m', strtotime($patient->claimed_at)) === date('Y-m');
            if (!$claimedThisMonth) {
                $requiredMedicines = [];
                $missingMedicines = [];
                $requiredMedicinesDetailed = [];
                $can_dispense = true;
                $binaryString = $prescription['medicines_binary'];
                $allMedicines = Medicine::orderBy('id')->get();

                for ($i = 0; $i < strlen($binaryString); $i++) {
                    if ($binaryString[$i] == '1') {
                        $medicine = $allMedicines[$i] ?? null;
                        if (!$medicine) continue;
                        $requiredMedicines[] = $medicine->id;
                        $requiredMedicinesDetailed[] = [$medicine->id, $medicine->name];
                        if ($medicine->amount_left < 1) {
                            $can_dispense = false;
                            $missingMedicines[] = [$medicine->id, $medicine->name];
                        }
                    }
                }

                if ($can_dispense) {
                    $patient->claimed_at = now();
                    $patient->save();

                    $pending_transaction = new PendingTransaction();
                    $pending_transaction->fill([
                        'scan_id' => $data['uin'],
                        'transaction_hash' => hash('sha256', random_bytes(32)),
                    ]);
                    $pending_transaction->save();
                    echo json_encode([
                        'status' => 'success',
                        'message' => 'Clear to dispense for patient',
                        'uin' => $uin,
                        'eligible' => true,
                        'found' => true,
                        'mosip_verified' => true,
                        'face_match' => $faceMatch,
                        'prescription' => (bool) $prescription,
                        'can_claim' => true,
                        'medicines' => $requiredMedicines,
                        'can_dispense' => $can_dispense,
                        'transaction_hash' => $pending_transaction['transaction_hash'],
                    ]) . "\n";
                } else {
                    $transaction = new Transaction();
                    $transaction->fill([
                        'scan_id' => $uin,
                        'transaction' => "Patient " . $uin . " required medicine stock insufficient. Out of stock medicines: " . json_encode($missingMedicines),
                    ]);
                    $transaction->save();
                    echo json_encode([
                        'status' => 'error',
                        'message' => 'Cannot dispense all required medicines',
                        'uin' => $uin,
                        'eligible' => true,
                        'found' => true,
                        'mosip_verified' => true,
                        'face_match' => $faceMatch,
                        'prescription' => (bool) $prescription,
                        'can_claim' => true,
                        'can_dispense' => $can_dispense,
                        'missing_medicines' => $missingMedicines,
                    ]) . "\n";
                }
            } else {
                $transaction = new Transaction();
                $transaction->fill([
                    'scan_id' => $uin,
                    'transaction' => "Patient " . $uin . " has already claimed medicines for this month. Will not dispense.",
                ]);
                $transaction->save();
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Already claimed for this month',
                    'uin' => $uin,
                    'eligible' => true,
                    'found' => true,
                    'mosip_verified' => true,
                    'face_match' => $faceMatch,
                    'prescription' => (bool) $prescription,
                    'can_claim' => false,
                ]) . "\n";
            }
        } else {
            $hasExpired = Prescription::where('patient_id', $patient->id)
                ->where('expires_at', '<=', now())
                ->exists();
            $reason  = $hasExpired ? 'expired_prescription' : 'no_prescription';
            $message = $hasExpired ? 'Prescription has expired' : 'Patient has no valid prescription';

            $transaction = new Transaction();
            $transaction->fill([
                'scan_id' => $uin,
                'transaction' => "Patient " . $uin . " — " . $message . ". Will not dispense.",
            ]);
            $transaction->save();
            echo json_encode([
                'status' => 'error',
                'message' => $message,
                'reason' => $reason,
                'uin' => $uin,
                'eligible' => true,
                'found' => true,
                'mosip_verified' => true,
                'face_match' => $faceMatch,
                'prescription' => false,
            ]) . "\n";
        }
    }

    private function parseDob(?string $dob): ?Carbon
    {
        if (!$dob) {
            return null;
        }
        foreach (['Y/m/d', 'Y-m-d', 'd/m/Y', 'd-m-Y'] as $format) {
            try {
                return Carbon::createFromFormat($format, $dob)->startOfDay();
            } catch (\Exception $e) {
                continue;
            }
        }
        try {
            return Carbon::parse($dob)->startOfDay();
        } catch (\Exception $e) {
            return null;
        }
    }

    private function splitName(?string $name): array
    {
        if (!$name) {
            return [null, null, null];
        }
        $parts = preg_split('/\s+/', trim($name));
        if (count($parts) === 1) {
            return [$parts[0], null, null];
        }
        if (count($parts) === 2) {
            return [$parts[0], null, $parts[1]];
        }
        $first  = array_shift($parts);
        $last   = array_pop($parts);
        $middle = implode(' ', $parts);
        return [$first, $middle, $last];
    }
    public function receiveDebug(Request $request)
    {
        // $receivedString = trim($request->getContent());
        $data = json_decode($request->getContent(), true);

        $patientExists = Patient::where('scan_id', $data['uin'])->exists();
        if ($patientExists) {
            $patient = Patient::where('scan_id', $data['uin'])->first();
            $latest_prescription = Prescription::where('patient_id', $patient->id)
                ->where('expires_at', '>', now())  // only non-expired prescriptions
                ->orderBy('created_at', 'desc')
                ->first(); //get the latest from all the non-expired ones
            if ($latest_prescription) {
                $claimedMonth = $patient->claimed_at ? date('m', strtotime($patient->claimed_at)) : "00";
                $currentMonth = date('m');
                if ($claimedMonth < $currentMonth) {
                    $medicineBinaries = [];
                    $requiredMedicines = [];
                    $requiredMedicinesDetailed = [];
                    $missingMedicines = [];
                    $can_dispense = true;
                    $binaryString = $latest_prescription['medicines_binary'];
                    $allMedicines = Medicine::orderBy('id')->get();

                    for ($i = 0; $i < strlen($binaryString); $i++) {
                        if ($binaryString[$i] == '1') {
                            $medicine = $allMedicines[$i] ?? null;
                            if (!$medicine) continue;
                            $medicineBinaries[] = str_pad(decbin($i + 1), 3, '0', STR_PAD_LEFT);
                            $requiredMedicines[] = $medicine->id;
                            $requiredMedicinesDetailed[] = [$medicine->id, $medicine->name];
                            if ($medicine->amount_left < 1) {
                                $can_dispense = false;
                                $missingMedicines[] = [$medicine->id, $medicine->name];
                            }
                        }
                    }
                    if($can_dispense) {
                        $pending_transaction = new PendingTransaction();
                        $pending_transaction->fill([
                            'scan_id' => $data['uin'],
                            'transaction_hash' => hash('sha256', random_bytes(32)),
                        ]);
                        $pending_transaction->save();
                        return response()->json([
                            'status' => 'success',
                            'message' => 'Clear to dispense for patient',
                            'scan_id' => $data['uin'],
                            'found' => true,
                            'prescription' => (bool) $latest_prescription,
                            'can_claim' => true,
                            'can_dispense' => $can_dispense,
                            'medicines' => $requiredMedicines,
                            'transaction_hash' => $pending_transaction['transaction_hash']
                        ], 200);
                    } else {
                        $transaction = new Transaction();
                        $transaction->fill([
                            'scan_id' => $data['uin'],
                            'transaction' => "Failed to dispense. Required medicine stock insufficient. Out of stock medicines: " . json_encode($missingMedicines),
                        ]);
                        $transaction->save();
                        return response()->json([
                            'status' => 'error',
                            'message' => 'Cannot dispense all required medicines',
                            'scan_id' => $data['uin'],
                            'found' => true,
                            'prescription' => (bool) $latest_prescription,
                            'can_claim' => true,
                            'can_dispense' => $can_dispense,
                            'missing_medicines' => $missingMedicines
                        ], 200);
                    }
                    
                } else {
                    $transaction = new Transaction();
                    $transaction->fill([
                        'scan_id' => $data['uin'],
                        'transaction' => "Has already claimed medicines for this month. Will not dispense."
                    ]);
                    $transaction->save();
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Already claimed for this month',
                        'scan_id' => $data['uin'],
                        'found' => true,
                        'prescription' => (bool) $latest_prescription,
                        'can_claim' => false,
                    ], 200);
                }
            } else {
                $hasExpired = Prescription::where('patient_id', $patient->id)
                    ->where('expires_at', '<=', now())
                    ->exists();
                $reason  = $hasExpired ? 'expired_prescription' : 'no_prescription';
                $message = $hasExpired ? 'Prescription has expired' : 'Patient has no valid prescription';

                $transaction = new Transaction();
                $transaction->fill([
                    'scan_id' => $data['uin'],
                    'transaction' => "Patient " . $data['uin'] . " — " . $message . ". Will not dispense.",
                ]);
                $transaction->save();
                return response()->json([
                    'status' => 'error',
                    'message' => $message,
                    'reason' => $reason,
                    'scan_id' => $data['uin'],
                    'found' => true,
                    'prescription' => false,
                ], 200);
            }
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Patient not found in database',
                'scan_id' => $data['uin'],
                'found' => false,
            ], 200);
        }
    }

    public function dispensed(Request $request)
    {
        $data = json_decode($request->getContent(), true);

        if (!is_array($data) || !isset($data['uin'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Request body must be JSON with a uin field',
            ], 400);
        }

        if (PendingTransaction::where('transaction_hash', $data['transaction_hash'])->exists()) {
            $pending_transaction = PendingTransaction::where('transaction_hash', $data['transaction_hash'])
            ->first();
            $scanId = $pending_transaction->scan_id;
            $pending_transaction->delete();

            for ($i = 0; $i < count($data['medicines']); $i++) {
                $row = Medicine::find($data['medicines'][$i]);
                if ($row->amount_left >= 1) {
                    $row->decrement('amount_left');
                }
            }

            $transaction = new Transaction();
            $transaction->fill([
                'scan_id' => $scanId,
                'transaction' => "Successfully claimed medicine. Dispensed medicines: " . json_encode($data['medicines']),
            ]);
            $transaction->save();

            return response()->json([
                'status' => "success",
                'message' => "Transaction ".$data['transaction_hash']." resolved successfully. Medicine counts decremented.",
                'uin' => $data['uin'],
                'medicines' => $data['medicines'],
            ], 200);
        } else {
            return response()->json([
                'status' => "error",
                'message' => "No transaction with transaction hash found.",
                'uin' => $data['uin'],
                'transaction_hash' => $data['transaction_hash'],
                'medicines' => $data['medicines']
            ], 200);
        }
    }
}