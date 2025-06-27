<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Appointment;
use App\Models\Visit;
use App\Models\LabResult;
use App\Models\Prescription;
use App\Models\Vital;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();
        $patientId = $user->id;

        $nextAppointment = Appointment::where('patient_id', $patientId)
            ->where('date', '>=', now())
            ->orderBy('date')
            ->orderBy('time')
            ->with('doctor')
            ->first();

        $recentVisit = Visit::where('patient_id', $patientId)
            ->orderByDesc('date')
            ->with('doctor')
            ->first();

        $latestLabResult = LabResult::where('patient_id', $patientId)
            ->orderByDesc('date')
            ->first();

        $currentPrescription = Prescription::where('patient_id', $patientId)
            ->orderByDesc('start_date')
            ->first();

        $latestVitals = Vital::where('patient_id', $patientId)
            ->orderByDesc('recorded_at')
            ->first();

        $vitalsTrend = Vital::where('patient_id', $patientId)
            ->orderBy('recorded_at')
            ->take(10)
            ->get();

        $labResultsTrend = LabResult::where('patient_id', $patientId)
            ->orderBy('date')
            ->take(10)
            ->get();

        return Inertia::render('Dashboard', [
            'user' => $user,
            'nextAppointment' => $nextAppointment,
            'recentVisit' => $recentVisit,
            'latestLabResult' => $latestLabResult,
            'currentPrescription' => $currentPrescription,
            'latestVitals' => $latestVitals,
            'vitalsTrend' => $vitalsTrend,
            'labResultsTrend' => $labResultsTrend,
        ]);
    }
} 