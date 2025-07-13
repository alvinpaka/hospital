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

        // Redirect staff to their dashboard
        if (in_array($user->role, ['doctor', 'nurse'])) {
            return redirect()->route('staff.dashboard');
        }

        // Only patients should see this dashboard
        if ($user->role !== 'patient') {
            abort(403, 'Unauthorized access');
        }

        $patientId = $user->id;

        $nextAppointment = Appointment::where('patient_id', $patientId)
            ->where('date', '>=', today())
            ->whereIn('status', ['pending', 'confirmed'])
            ->orderBy('date')
            ->orderBy('time')
            ->with('doctor')
            ->first();

        // Get all upcoming appointments for the count
        $upcomingAppointments = Appointment::where('patient_id', $patientId)
            ->where('date', '>=', today())
            ->whereIn('status', ['confirmed'])
            ->orderBy('date')
            ->orderBy('time')
            ->with('doctor')
            ->get();

        $recentVisit = Visit::where('patient_id', $patientId)
            ->orderByDesc('date')
            ->with('doctor')
            ->first();

        $latestLabResult = LabResult::where('patient_id', $patientId)
            ->orderByDesc('date')
            ->first();

        $currentPrescription = Prescription::where('patient_id', $patientId)
            ->orderByDesc('start_date')
            ->with('drug')
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
            'upcomingAppointments' => $upcomingAppointments,
            'recentVisit' => $recentVisit,
            'latestLabResult' => $latestLabResult,
            'currentPrescription' => $currentPrescription,
            'latestVitals' => $latestVitals,
            'vitalsTrend' => $vitalsTrend,
            'labResultsTrend' => $labResultsTrend,
        ]);
    }
} 