<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Record;
use App\Models\Sex;
use App\Models\StudentType;
use App\Models\NameSuffix;
use App\Models\LatinHonors;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Log;

class RecordController extends Controller
{
    public function read(Request $request): Response
    {
        return Inertia::render('UserInformation', [
            'record' => Record::find($request->user()->record_id)->toArray(),
            'dropdownOptions' => [
                'sexes' => Sex::all(["id", "sex"]),
                'types' => StudentType::all(['id','type']),
                'suffixes' => NameSuffix::all(["id", "suffix"]),
                'latin_honors' => LatinHonors::all(["id","honor"]),
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
                'student_number' => "Student Number",
                'type_id' => "Student Type",
                'last_name' => "Last Name",
                'first_name' => "First Name",
                'middle_name' => "Middle Name",
                'maiden_name' => "Maiden Name",
                'email' => "Email Address",
                'alt_email' => "Alternate Email",
                'phone_number' => "Mobile Number",
                'address' => "Address",
                'sex_id' => "Sex",
                'suffix_id' => "Suffix",
                'latin_honors_id' => "Latin Honors",
                'batch' => "Batch",
                'created_at' => "Created At",
                'updated_at' => "Updated At",
                'actions' => "Actions"
            ],
            'type' => [
                'id' =>  'numeric',
                'student_number' =>  'numeric',
                'phone_number' => "numeric",
                'batch' =>  'numeric',
                'created_at' => "datetime",
                'updated_at' =>  "datetime"
            ],
            'dropdown' => [
                'sex_id' => 'sex',
                'type_id' => 'type',
                'suffix_id' => 'suffix',
                'latin_honors_id' => 'honor',
            ]
        ];
        $data = Record::all();

        return Inertia::render('AdminRecords', [
            'data' => $data,
            'headers' => $headers,
            'dropdownOptions' => [
                'sex_id' => Sex::all(["id", "sex"]),
                'type_id' => StudentType::all(['id','type']),
                'suffix_id' => NameSuffix::all(["id", "suffix"]),
                'latin_honors_id' => LatinHonors::all(["id","honor"]),
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
        $record = new Record();
        $record->fill($request->all());
        $record->save();
        return redirect()->back()->with([
            'success' => 'Record updated successfully.',
            'data' => Record::all(), // Return fresh records
            // 'records' => Record::all()
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function userUpdate(Request $request)
    {
        $user = auth()->user();
        $id = $request->user()->id;
        $record = Record::find($id);

        $request->validate([            
            'first_name' => 'required',
            'last_name' => 'required',
            'suffix_id' => 'required',
            'sex_id' => 'required',
            'address' => 'required',
            'phone_number' => 'required',
            'email' => 'required',
            'student_number' => 'required|numeric',
            'type_id' => 'required',
            'batch' => 'required',
            'latin_honors_id' => 'required'
        ]);

        $record->update($request->all());

        return redirect()->route('user.information')
            ->with('success', 'Record updated successfully.');
    }
    public function adminUpdate(Request $request)
    {
        $id = $request->input('id');
        $record = Record::find($id);

        $request->validate([            
            'first_name' => 'required',
            'last_name' => 'required',
            'suffix_id' => 'required',
            'sex_id' => 'required',
            'address' => 'required',
            'phone_number' => 'required',
            'email' => 'required',
            'student_number' => 'required|numeric',
            'type_id' => 'required',
            'batch' => 'required',
            'latin_honors_id' => 'required'
        ]);

        $record->update($request->all());

        return redirect()->back()->with([
            'success' => 'Record updated successfully.',
            'records' => Record::all() // Return fresh records
        ]);
            
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $record = Record::find($id);
        // Log::channel('stderr')->Info("Record to delete [".$id."]");
        $record->delete();
        return redirect()->back()->with('success','Record Succesfully Deleted');
    }
}
