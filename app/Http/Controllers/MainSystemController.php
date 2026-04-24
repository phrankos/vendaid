<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\Prescription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MainSystemController extends Controller
{
    public function receive(Request $request)
    {
        $receivedString = trim($request->getContent());

        // Log::channel('stderr')->Info('Data received: ' . $receivedString . "\nChecking if Patient exists...");
        $patientExists = Patient::where('scan_id', $receivedString)->exists();
        if ($patientExists) {
            $patient = Patient::where('scan_id', $receivedString)->first();
            // Log::channel('stderr')->Info('Patient found: ' . $patient);
            $latest_prescription = Prescription::where('patient_id', $patient->id)
                ->where('expires_at', '>', now())  // only non-expired prescriptions
                ->orderBy('created_at', 'desc')
                ->first(); //get the latest from all the non-expired ones
            // Log::channel('stderr')->Info('Prescription found : ' . $latest_prescription);
            if ($latest_prescription) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Patient exists in database',
                    'scan_id' => $receivedString,
                    'found' => true,
                    'prescription' => true,
                    'medicine_binary' => $latest_prescription['medicines_binary']
                ], 200);
            } else {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Patient exists in database',
                    'scan_id' => $receivedString,
                    'found' => true,
                    'prescription' => false,
                ], 200);
            }
        } else {
            // Log::channel('stderr')->Info('Patient not in database\nCreating new Patient with '. $receivedString . 'as indentifier...'); 
            return response()->json([
                'status' => 'error',
                'message' => 'Patient not found in database',
                'scan_id' => $receivedString,
                'found' => false,
            ], 200);
        }
    }
}
