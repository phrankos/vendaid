<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\Medicine;

use Illuminate\Support\Facades\Log;

class MedicineController extends Controller
{
    private $headers = [
        'name' => [
            'id' => "ID",
            'Medicine' => "Medicine",
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
            'start_date' => "date",
            'end_date' => "date", 
            'created_at' => "datetime",
            'updated_at' =>  "datetime"
        ],
        'dropdown' => [
            'is_current' => 'state',
        ]
    ];
    private $dropdownOptions = [];

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $headers = [
            'name' => [
                'id' => "ID",
                'name' => "Name",
                'amount_left' => "Amount Left",
                'created_at' => "Created At",
                'updated_at' => "Updated At",
                'actions' => "Actions"
            ],
            'type' => [
                'id' =>  'numeric',
                'amount_left' => "numeric",
                'created_at' => "datetime",
                'updated_at' =>  "datetime"
            ],
            'dropdown' => []
        ];
        $data = Medicine::all();

        return Inertia::render('Medicines', [
            'data' => $data,
            'headers' => $headers,
            'dropdownOptions' => []
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Log::channel('stderr')->Info("store request");
        // Log::channel('stderr')->Info($request);
        $data = new Medicine();
        $data->fill($request->all());
        $data->save();
        

        return redirect()->back()->with([
            'success' => 'Medicine Successfully Created.'
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $id = $request->input('id');
        $row = Medicine::find($id);

        $request->validate([
            'name' => "required",
            'amount_left' => "required",
        ]);

        $row->update($request->all());

        return redirect()->back()->with([
            'success' => 'Medicine Successfully Updated.',
            'data' => Medicine::all()
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $data = Medicine::find($id);
        $data->delete();
        return redirect()->back()->with('success','Medicine Succesfully Deleted');
    }
}
