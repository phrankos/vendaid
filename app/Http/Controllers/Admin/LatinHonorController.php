<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LatinHonors;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LatinHonorController extends Controller
{
    // Display all Latin Honors
    public function index()
    {
        $honors = LatinHonors::all();
        return Inertia::render('AdminMisc', ['honors' => $honors]);
    }

    // Store a new Latin Honor
    public function store(Request $request)
    {
        $request->validate([
            'honor' => 'nullable|string|max:100',
        ]);
        LatinHonors::create($request->only('honor'));
        return redirect()->back()->with('success', 'Latin Honor added.');
    }

    // Update an existing Latin Honor
    public function update(Request $request, $id)
    {
        $request->validate([
            'honor' => 'nullable|string|max:100',
        ]);
        $honor = LatinHonors::findOrFail($id);
        $honor->update($request->only('honor'));
        return redirect()->back()->with('success', 'Latin Honor updated.');
    }

    // Delete a Latin Honor
    public function destroy($id)
    {
        $honor = LatinHonors::findOrFail($id);
        $honor->delete();
        return redirect()->back()->with('success', 'Latin Honor deleted.');
    }
}