<?php

namespace App\Http\Controllers;

use App\Models\Vital;
use Illuminate\Http\Request;
use Inertia\Inertia;

class VitalController extends Controller
{
    public function index()
    {
        $vitals = Vital::where('patient_id', auth()->id())->get();
        return Inertia::render('vitals', [
            'vitals' => $vitals,
        ]);
    }

    public function store(Request $request)
    {
        // Store a new vital
    }

    public function show(Vital $vital)
    {
        // Show a single vital
    }

    public function update(Request $request, Vital $vital)
    {
        // Update a vital
    }

    public function destroy(Vital $vital)
    {
        // Delete a vital
    }
}
