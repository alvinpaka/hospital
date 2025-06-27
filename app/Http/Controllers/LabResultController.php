<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LabResult;
use Inertia\Inertia;

class LabResultController extends Controller
{
    public function index(Request $request)
    {
        $labResults = LabResult::where('patient_id', $request->user()->id)->get();
        return Inertia::render('lab-results', [
            'labResults' => $labResults
        ]);
    }
}
