<?php


namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StudentType;
use App\Models\LatinHonors;
use App\Models\NameSuffix;
use App\Models\Sex;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StudentTypeController extends Controller
{
    // Display all tables
    public function index()
    {
        return Inertia::render('AdminMisc', [
            'honors' => LatinHonors::all(),
            'suffixes' => NameSuffix::all(),
            'sexes' => Sex::all(),
            'studentTypes' => StudentType::all(),
        ]);
    }

    // Store a new Student Type
    public function store(Request $request)
    {
        $request->validate([
            'type' => 'nullable|string|max:100',
        ]);
        StudentType::create($request->only('type'));
        return redirect()->back()->with('success', 'Student Type added.');
    }

    // Update an existing Student Type
    public function update(Request $request, $id)
    {
        $request->validate([
            'type' => 'nullable|string|max:100',
        ]);
        $type = StudentType::findOrFail($id);
        $type->update($request->only('type'));
        return redirect()->back()->with('success', 'Student Type updated.');
    }

    // Delete a Student Type
    public function destroy($id)
    {
        $type = StudentType::findOrFail($id);
        $type->delete();
        return redirect()->back()->with('success', 'Student Type deleted.');
    }
}