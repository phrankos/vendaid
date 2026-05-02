<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\Prescription;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class MainSystemController extends Controller
{
    private const MOSIP_SERVICE_URL = 'http://127.0.0.1:5000';
    private const ESP32_CAM_URL = 'http://192.168.100.217/capture_b64';
    private const CAM_PRE_CAPTURE_DELAY_MS = 1500;
    private const SENIOR_AGE = 60;
    // private const ALLOWED_BARANGAYS = ['San Jose'];
    private const ALLOWED_BARANGAYS = [];
    private const GENDER_TO_SEX_ID = ['Male' => 1, 'Female' => 2];

    public function receive(Request $request)
    {
        $data = json_decode($request->getContent(), true);

        if (!is_array($data) || !isset($data['uin'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Request body must be JSON with a uin field',
            ], 400);
        }

        // Give the user a moment to look at the camera after scanning,
        // then pull a fresh frame from the ESP32-CAM and bundle it in.
        if (empty($data['image_base64'])) {
            usleep(self::CAM_PRE_CAPTURE_DELAY_MS * 1000);
            $img = $this->fetchCameraImage();
            if ($img === null) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Camera capture failed',
                    'reason' => 'camera_unreachable',
                ], 502);
            }
            $data['image_base64'] = $img;
        }

        return $this->handleMosipScan($data);
    }

    private function fetchCameraImage(): ?string
    {
        try {
            $resp = Http::timeout(15)->get(self::ESP32_CAM_URL);
        } catch (\Exception $e) {
            Log::warning('cam fetch failed: '.$e->getMessage());
            return null;
        }
        if (!$resp->ok()) {
            Log::warning('cam returned HTTP '.$resp->status());
            return null;
        }
        $body = trim($resp->body());
        return $body === '' ? null : $body;
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

        // Age restriction
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

        try {
            $resp = Http::timeout(60)->post(self::MOSIP_SERVICE_URL.'/verify', $payload);
            $kyc  = $resp->json();
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'MOSIP service unavailable',
                'mosip_verified' => false,
            ], 503);
        }

        if (!($kyc['verified'] ?? false)) {
            return response()->json([
                'status' => 'error',
                'message' => 'MOSIP identity verification failed',
                'uin' => $uin,
                'eligible' => false,
                'mosip_verified' => false,
                'errors' => $kyc['errors'] ?? [],
            ], 200);
        }

        $faceMatch = $kyc['face_match'] ?? null;
        if ($faceMatch !== null && !($faceMatch['verified'] ?? false)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Face does not match MOSIP record',
                'uin' => $uin,
                'eligible' => false,
                'mosip_verified' => true,
                'face_match' => $faceMatch,
            ], 200);
        }

        // barangay restriction
        $barangay = $kyc['kyc_data']['zone_eng'] ?? null;
        if (!empty(self::ALLOWED_BARANGAYS)
            && (!$barangay || !in_array($barangay, self::ALLOWED_BARANGAYS, true))) {
            return response()->json([
                'status' => 'error',
                'message' => 'Patient is not in an eligible barangay',
                'uin' => $uin,
                'eligible' => false,
                'reason' => 'wrong_barangay',
                'barangay' => $barangay,
            ], 200);
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

        return response()->json([
            'status' => 'success',
            'message' => 'Patient verified',
            'uin' => $uin,
            'eligible' => true,
            'found' => true,
            'mosip_verified' => true,
            'face_match' => $faceMatch,
            'prescription' => (bool) $prescription,
            'medicine_binary' => $prescription?->medicines_binary,
        ], 200);
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
}
