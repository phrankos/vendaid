<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sex;
use App\Models\Patient;
use Inertia\Inertia;
use Inertia\Response;

// use Illuminate\Support\Facades\Log;

class PatientController extends Controller
{
    public function read(Request $request): Response
    {
        return Inertia::render('UserInformation', [
            'patient' => Patient::find($request->user()->patient_id)->toArray(),
            'dropdownOptions' => [
                'sexes' => Sex::all(["id", "sex"]),
            ]
        ]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $headers = [
            'name' => [
                'id' => "ID",
                'scan_id' => "Scan ID",
                'last_name' => "Last Name",
                'first_name' => "First Name",
                'middle_name' => "Middle Name",
                'sex_id' => "Sex",
                // 'created_by' => "Created By",
                // 'updated_by' => "Updated By",
                'created_by_name' => "Created By",
                'updated_by_name' => "Updated By",
                'claimed_at' => "Claimed At",
                'created_at' => "Created At",
                'updated_at' => "Updated At",
                'actions' => "Actions"
            ],
            'type' => [
                'id' =>  'numeric',
                'claimed_at' =>  'date',
                'created_at' => "datetime",
                'updated_at' =>  "datetime"
            ],
            'dropdown' => [
                'sex_id' => 'sex',
            ]
        ];
        $data = Patient::all();

        return Inertia::render('Patients', [
            'data' => $data,
            'headers' => $headers,
            'dropdownOptions' => [
                'sex_id' => Sex::all(["id", "sex"]),
            ]
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Log::channel('stderr')->Info("store request");
        // Log::channel('stderr')->Info($request);
        // Log::channel('stderr')->Info($request->user()['email']);
        $patient = new Patient();
        $patient->fill($request->all());
        $patient['created_by'] = $request->user()['id'];
        $patient['updated_by'] = $request->user()['id'];
        $patient->save();
        return redirect()->back()->with([
            'success' => 'Patient updated successfully.',
            'data' => Patient::all(), // Return fresh data
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $id = $request->input('id');
        $patient = Patient::find($id);

        $request->validate([            
            'first_name' => 'required',
            'last_name' => 'required',
            'sex_id' => 'required',
        ]);

        $patient['updated_by'] = $request->user()['id'];
        $patient->update($request->all());

        return redirect()->back()->with([
            'success' => 'Patient updated successfully.',
            'data' => Patient::all(), // Return fresh data
        ]);
            
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $patient = Patient::find($id);
        $patient->delete();
        return redirect()->back()->with('success','Patient Succesfully Deleted');
    }
}
