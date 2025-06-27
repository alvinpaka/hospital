<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Visit;
use Inertia\Inertia;

class VisitController extends Controller
{
    public function index(Request $request)
    {
        $visits = Visit::where('patient_id', $request->user()->id)->get();
        return Inertia::render('visits', [
            'visits' => $visits
        ]);
    }
}
