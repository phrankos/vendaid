<?php


namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NameSuffix;
use Illuminate\Http\Request;
use Inertia\Inertia;

class NameSuffixController extends Controller
{
    // Display all Name Suffixes
    public function index()
    {
        $suffixes = NameSuffix::all();
        // Pass both honors and suffixes to the page
        return Inertia::render('AdminMisc', [
            'honors' => \App\Models\LatinHonors::all(),
            'suffixes' => $suffixes,
        ]);
    }

    // Store a new Name Suffix
    public function store(Request $request)
    {
        $request->validate([
            'suffix' => 'nullable|string|max:100',
        ]);
        NameSuffix::create($request->only('suffix'));
        return redirect()->back()->with('success', 'Name Suffix added.');
    }

    // Update an existing Name Suffix
    public function update(Request $request, $id)
    {
        $request->validate([
            'suffix' => 'nullable|string|max:100',
        ]);
        $suffix = NameSuffix::findOrFail($id);
        $suffix->update($request->only('suffix'));
        return redirect()->back()->with('success', 'Name Suffix updated.');
    }

    // Delete a Name Suffix
    public function destroy($id)
    {
        $suffix = NameSuffix::findOrFail($id);
        $suffix->delete();
        return redirect()->back()->with('success', 'Name Suffix deleted.');
    }
}