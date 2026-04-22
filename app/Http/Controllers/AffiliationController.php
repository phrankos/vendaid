<?php

namespace App\Http\Controllers;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\Record;
use App\Models\Affiliation;
use Illuminate\Support\Facades\Log;

use Illuminate\Http\Request;

class AffiliationController extends Controller
{
    private $headers = [
        'name' => [
            'id' => "ID",
            'record_id' => "Record ID",
            'affiliation' => "Affiliation",
            'position' => "Position",
            'start_date' => "Start Date",
            'end_date' => "End Date",
            'is_current' => "Is Current",
            'created_at' => "Created At",
            'updated_at' => "Updated At",
            'actions' => "Actions"
        ],
        'type' => [
            'id' =>  'numeric',
            'record_id' =>  'numeric',     
            'start_date' => "date",
            'end_date' => "date", 
            'created_at' => "datetime",
            'updated_at' =>  "datetime"
        ],
        'dropdown' => [
            'is_current' => 'state',
        ]
    ];
    private $dropdownOptions = [
        'is_current' => [
            [
                'id' => 0,
                'state' => false
            ],
            [
                'id' => 1,
                'state' => true
            ]
        ]
    ];

    public function read(Request $request): Response
    {
        // $data = Affiliation::find($request->user()->record_id)->toArray();
        $data = Affiliation::where('record_id', $request->user()->record_id)->get();

        return Inertia::render('UserAffiliations', [
            'data' => $data,
            'headers' => $this->headers,
            'dropdownOptions' => $this->dropdownOptions
        ]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = Affiliation::all();

        return Inertia::render('AdminAffiliations', [
            'data' => $data,
            'headers' => $this->headers,
            'dropdownOptions' => $this->dropdownOptions
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Log::channel('stderr')->Info("store request");
        // Log::channel('stderr')->Info($request);
        $data = new Affiliation();
        $data->fill($request->all());
        
        if ($request->input('record_id') == null) {
            // USER
            $data->record_id = auth()->user()->record_id;
        }
        $data->save();
        

        return redirect()->back()->with([
            'success' => 'Affiliation Successfully Created.'
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function adminUpdate(Request $request)
    {
        $id = $request->input('id');
        $row = Affiliation::find($id);

        $request->validate([
            'record_id' => "required",
            'affiliation' => "required",
            'position' => "required",
            'start_date' => "required",
            'end_date' => "required",
            'is_current' => "required",
        ]);

        $row->update($request->all());

        return redirect()->back()->with([
            'success' => 'Affiliation Successfully Updated.',
            'data' => Affiliation::all()
        ]);
    }
    
    public function userUpdate(Request $request)
    {
        $user_recordID = auth()->user()->record_id;
        $id = $request->input('id');
        $row = Affiliation::find($id);

        if ($user_recordID != $row['record_id']) {
            return redirect()->back()->with([
                'error' => 'User Record ID does not match the Affiliation Record ID.',
            ]);
        }

        // Log::channel('stderr')->Info("user update request");
        Log::channel('stderr')->Info($request);
        // Log::channel('stderr')->Info("User Record ID: [".$user_recordID."]");
        // Log::channel('stderr')->Info("Row Record ID: [".$row['record_id']."]");

        $request->validate([
            'affiliation' => "required",
            'position' => "required",
            'start_date' => "required",
            'end_date' => "required",
            'is_current' => "required",
        ]);

        $row->update($request->all());

        return redirect()->back()->with([
            'success' => 'Affiliation Successfully Updated.',
            'data' => Affiliation::all() // Return fresh records
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $data = Affiliation::find($id);
        $data->delete();
        return redirect()->back()->with('success','Affiliation Succesfully Deleted');
    }
}
