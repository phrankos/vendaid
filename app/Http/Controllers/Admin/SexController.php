<?php


namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sex;
use App\Models\LatinHonors;
use App\Models\NameSuffix;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SexController extends Controller
{
    // Display all Sexes, Honors, and Suffixes
    public function index()
    {
        return Inertia::render('AdminMisc', [
            'honors' => LatinHonors::all(),
            'suffixes' => NameSuffix::all(),
            'sexes' => Sex::all(),
        ]);
    }

    // Store a new Sex
    public function store(Request $request)
    {
        $request->validate([
            'sex' => 'nullable|string|max:100',
        ]);
        Sex::create($request->only('sex'));
        return redirect()->back()->with('success', 'Sex added.');
    }

    // Update an existing Sex
    public function update(Request $request, $id)
    {
        $request->validate([
            'sex' => 'nullable|string|max:100',
        ]);
        $sex = Sex::findOrFail($id);
        $sex->update($request->only('sex'));
        return redirect()->back()->with('success', 'Sex updated.');
    }

    // Delete a Sex
    public function destroy($id)
    {
        $sex = Sex::findOrFail($id);
        $sex->delete();
        return redirect()->back()->with('success', 'Sex deleted.');
    }
}