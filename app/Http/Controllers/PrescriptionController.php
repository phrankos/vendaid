<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\Prescription;
use App\Models\Medicine;
use Illuminate\Support\Facades\Log;

class PrescriptionController extends Controller
{
    private $headers = [
            'name' => [
                'id' => "ID",
                'patient_id' => "Patient",
                'medicines_binary' => "Medicines",
                'issued_by_name' => "Issued By",
                'expires_at' => "Expires At",
                'created_at' => "Created At",
                'updated_at' => "Updated At",
                'actions' => "Actions"
            ],
            'type' => [
                'id' =>  'numeric',
                'medicines_binary' => "bin_loop",
                'expires_at' => "date",
                'created_at' => "datetime",
                'updated_at' =>  "datetime"
            ],
            'dropdown' => [
                'patient_id' => "patient_name",
                'medicines_binary' => "medicines",
            ]
        ];

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = Prescription::all();
        $medicines = Medicine::all(['id', 'name']);

        return Inertia::render('Prescriptions', [
            'data' => $data,
            'headers' => $this->headers,
            'dropdownOptions' => [
                'patient_id' => Patient::all(["id", "last_name", "first_name", "middle_name"])->map(function($patient) {
                    return [
                        'id' => $patient->id,
                        'patient_name' => $patient->last_name . ', ' . $patient->first_name . ' ' . $patient->middle_name
                    ];
                }),
            ],
            'medicines' => $medicines
        ]);
    }

    public function store(Request $request)
    {
        $data = new Prescription();
        $data->fill($request->all());
        $data['issued_by'] = $request->user()['id'];
        $data->save();
        

        return redirect()->back()->with([
            'success' => 'Prescription Successfully Created.'
        ]);
    }

    public function update(Request $request)
    {
        $id = $request->input('id');
        $prescription = Prescription::findOrFail($id);
        
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'medicines_binary' => 'required|string|regex:/^[01]+$/',
            'expires_at' => 'date'
        ]);
        
        $medicineCount = Medicine::count();
        if (strlen($validated['medicines_binary']) !== $medicineCount) {
            return redirect()->back()->withErrors([
                'medicines_binary' => 'Invalid medicine selection format.'
            ])->withInput();
        }
        
        $prescription->update($validated);
        
        $updated = Prescription::find($id);
        Log::channel('stderr')->info("Request: " . $request);
        Log::channel('stderr')->info("Updated binary: " . $prescription);
        // Log::channel('stderr')->info("Updated binary: " . $updated->medicines_binary);
        
        return redirect()->back()->with([
            'success' => 'Prescription Successfully Updated.'
        ]);
    }
    public function destroy(string $id)
    {
        $data = Prescription::find($id);
        $data->delete();
        return redirect()->back()->with('success','Prescription Succesfully Deleted');
    }
}
