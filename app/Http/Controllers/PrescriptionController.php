<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Prescription;
use Inertia\Inertia;

class PrescriptionController extends Controller
{
    public function index(Request $request)
    {
        $prescriptions = Prescription::where('patient_id', $request->user()->id)->get();
        return Inertia::render('prescriptions', [
            'prescriptions' => $prescriptions
        ]);
    }
}
