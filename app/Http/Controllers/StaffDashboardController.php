<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\User;
use App\Models\Appointment;
use App\Models\Visit;
use App\Models\LabResult;
use App\Models\Prescription;
use App\Models\Vital;
use App\Models\Billing;
use Illuminate\Support\Facades\DB;

class StaffDashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();

        // Ensure only staff can access
        if (!in_array($user->role, ['doctor', 'nurse'])) {
            abort(403, 'Unauthorized access');
        }

        // Get today's appointments for this staff member
        $todayAppointments = Appointment::where('doctor_id', $user->id)
            ->whereDate('date', today())
            ->with('patient')
            ->orderBy('time')
            ->get();

        // Get upcoming appointments
        $upcomingAppointments = Appointment::where('doctor_id', $user->id)
            ->where('date', '>=', today())
            ->where('status', 'confirmed')
            ->with('patient')
            ->orderBy('date')
            ->orderBy('time')
            ->take(5)
            ->get();

        // Get recent visits by this staff member
        $recentVisits = Visit::where('doctor_id', $user->id)
            ->with('patient')
            ->orderByDesc('date')
            ->take(5)
            ->get();

        // Get recent lab results assigned to this staff member
        $recentLabResults = LabResult::where('doctor', $user->name)
            ->with('patient')
            ->orderByDesc('date')
            ->take(5)
            ->get();

        // Get recent prescriptions by this staff member
        $recentPrescriptions = Prescription::where('doctor', $user->name)
            ->with('patient')
            ->orderByDesc('start_date')
            ->take(5)
            ->get();

        // Get recent vitals recorded by this staff member
        $recentVitals = Vital::where('recorded_by', $user->name)
            ->with('patient')
            ->orderByDesc('recorded_at')
            ->take(5)
            ->get();

        // Get statistics for this staff member
        $stats = $this->getStaffStats($user);

        // Get monthly performance data
        $monthlyStats = $this->getMonthlyStats($user);

        // Get patient count for this staff member
        $patientCount = $this->getPatientCount($user);

        return Inertia::render('StaffDashboard', [
            'user' => $user,
            'todayAppointments' => $todayAppointments,
            'upcomingAppointments' => $upcomingAppointments,
            'recentVisits' => $recentVisits,
            'recentLabResults' => $recentLabResults,
            'recentPrescriptions' => $recentPrescriptions,
            'recentVitals' => $recentVitals,
            'stats' => $stats,
            'monthlyStats' => $monthlyStats,
            'patientCount' => $patientCount,
        ]);
    }

    private function getStaffStats($user)
    {
        $currentMonth = now()->month;
        $currentYear = now()->year;

        return [
            'totalAppointments' => Appointment::where('doctor_id', $user->id)->count(),
            'thisMonthAppointments' => Appointment::where('doctor_id', $user->id)
                ->whereYear('date', $currentYear)
                ->whereMonth('date', $currentMonth)
                ->count(),
            'pendingAppointments' => Appointment::where('doctor_id', $user->id)
                ->where('status', 'pending')
                ->count(),
            'totalVisits' => Visit::where('doctor_id', $user->id)->count(),
            'thisMonthVisits' => Visit::where('doctor_id', $user->id)
                ->whereYear('date', $currentYear)
                ->whereMonth('date', $currentMonth)
                ->count(),
            'totalLabResults' => LabResult::where('doctor', $user->name)->count(),
            'totalPrescriptions' => Prescription::where('doctor', $user->name)->count(),
            'totalVitals' => Vital::where('recorded_by', $user->name)->count(),
        ];
    }

    private function getMonthlyStats($user)
    {
        $currentYear = now()->year;
        $months = [];

        for ($month = 1; $month <= 12; $month++) {
            $appointments = Appointment::where('doctor_id', $user->id)
                ->whereYear('date', $currentYear)
                ->whereMonth('date', $month)
                ->count();

            $visits = Visit::where('doctor_id', $user->id)
                ->whereYear('date', $currentYear)
                ->whereMonth('date', $month)
                ->count();

            $months[] = [
                'month' => date('M', mktime(0, 0, 0, $month, 1)),
                'appointments' => $appointments,
                'visits' => $visits,
            ];
        }

        return $months;
    }

    private function getPatientCount($user)
    {
        // Count patients assigned to this staff member via doctor_patient table
        return $user->doctorPatients()->count();
    }
} 