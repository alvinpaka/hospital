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
use App\Models\ActivityLog;
use App\Models\Billing;
use App\Models\BillingItem;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();

        // Ensure only admins can access
        if ($user->role !== 'admin') {
            abort(403, 'Unauthorized access');
        }

        // Get counts for dashboard widgets
        $totalPatients = User::where('role', 'patient')->count();
        $totalStaff = User::whereIn('role', ['doctor', 'nurse', 'admin'])->count();
        $totalAppointments = Appointment::count();
        $pendingAppointments = Appointment::where('status', 'pending')->count();
        $totalVisits = Visit::count();
        $totalLabResults = LabResult::count();
        $totalPrescriptions = Prescription::count();
        $totalVitals = Vital::count();

        // Get recent activities
        $recentActivities = ActivityLog::with('user')
            ->orderByDesc('created_at')
            ->take(10)
            ->get();

        // Get today's appointments
        $todayAppointments = Appointment::with(['patient', 'doctor'])
            ->whereDate('date', today())
            ->orderBy('time')
            ->take(5)
            ->get();

        // Get recent lab results
        $recentLabResults = LabResult::with('patient')
            ->orderByDesc('date')
            ->take(5)
            ->get();

        // Get recent prescriptions
        $recentPrescriptions = Prescription::with('patient')
            ->orderByDesc('start_date')
            ->take(5)
            ->get();

        // Get monthly statistics for charts
        $monthlyStats = $this->getMonthlyStats();

        // Get appointment status distribution and pending appointments data
        $appointmentStatusStats = [
            'statusCounts' => Appointment::select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->get(),
            'pending' => Appointment::with(['patient', 'doctor'])
                ->where('status', 'pending')
                ->orderBy('date')
                ->orderBy('time')
                ->get(),
        ];

        // Analytics & Reports Data
        $analytics = $this->getAnalyticsData();

        return Inertia::render('AdminDashboard', [
            'user' => $user,
            'stats' => [
                'totalPatients' => $totalPatients,
                'totalStaff' => $totalStaff,
                'totalAppointments' => $totalAppointments,
                'pendingAppointments' => $pendingAppointments,
                'totalVisits' => $totalVisits,
                'totalLabResults' => $totalLabResults,
                'totalPrescriptions' => $totalPrescriptions,
                'totalVitals' => $totalVitals,
            ],
            'recentActivities' => $recentActivities,
            'todayAppointments' => $todayAppointments,
            'recentLabResults' => $recentLabResults,
            'recentPrescriptions' => $recentPrescriptions,
            'monthlyStats' => $monthlyStats,
            'appointmentStatusStats' => $appointmentStatusStats,
            'analytics' => $analytics,
        ]);
    }

    private function getMonthlyStats()
    {
        $currentMonth = now()->month;
        $currentYear = now()->year;

        return [
            'appointments' => Appointment::whereYear('date', $currentYear)
                ->whereMonth('date', $currentMonth)
                ->count(),
            'visits' => Visit::whereYear('date', $currentYear)
                ->whereMonth('date', $currentMonth)
                ->count(),
            'labResults' => LabResult::whereYear('date', $currentYear)
                ->whereMonth('date', $currentMonth)
                ->count(),
            'prescriptions' => Prescription::whereYear('start_date', $currentYear)
                ->whereMonth('start_date', $currentMonth)
                ->count(),
        ];
    }

    private function getAnalyticsData()
    {
        $currentMonth = now()->month;
        $currentYear = now()->year;
        $lastMonth = now()->subMonth();

        // Patient Demographics
        $patientDemographics = [
            'total' => User::where('role', 'patient')->count(),
            'newThisMonth' => User::where('role', 'patient')
                ->whereYear('created_at', $currentYear)
                ->whereMonth('created_at', $currentMonth)
                ->count(),
            'verified' => User::where('role', 'patient')->whereNotNull('email_verified_at')->count(),
            'unverified' => User::where('role', 'patient')->whereNull('email_verified_at')->count(),
        ];

        // Appointment Analytics
        $appointmentAnalytics = [
            'total' => Appointment::count(),
            'thisMonth' => Appointment::whereYear('date', $currentYear)
                ->whereMonth('date', $currentMonth)
                ->count(),
            'lastMonth' => Appointment::whereYear('date', $lastMonth->year)
                ->whereMonth('date', $lastMonth->month)
                ->count(),
            'confirmed' => Appointment::where('status', 'confirmed')->count(),
            'pending' => Appointment::where('status', 'pending')->count(),
            'canceled' => Appointment::where('status', 'canceled')->count(),
            'avgPerDay' => Appointment::whereYear('date', $currentYear)
                ->whereMonth('date', $currentMonth)
                ->count() / now()->daysInMonth,
        ];

        // Staff Performance
        $staffPerformance = [
            'doctors' => User::where('role', 'doctor')->count(),
            'nurses' => User::where('role', 'nurse')->count(),
            'admins' => User::where('role', 'admin')->count(),
            'activeDoctors' => Appointment::whereNotNull('doctor_id')
                ->whereYear('date', $currentYear)
                ->whereMonth('date', $currentMonth)
                ->distinct('doctor_id')
                ->count('doctor_id'),
        ];

        // Medical Records Analytics
        $medicalRecordsAnalytics = [
            'visits' => [
                'total' => Visit::count(),
                'thisMonth' => Visit::whereYear('date', $currentYear)
                    ->whereMonth('date', $currentMonth)
                    ->count(),
            ],
            'labResults' => [
                'total' => LabResult::count(),
                'thisMonth' => LabResult::whereYear('date', $currentYear)
                    ->whereMonth('date', $currentMonth)
                    ->count(),
            ],
            'prescriptions' => [
                'total' => Prescription::count(),
                'thisMonth' => Prescription::whereYear('start_date', $currentYear)
                    ->whereMonth('start_date', $currentMonth)
                    ->count(),
            ],
            'vitals' => [
                'total' => Vital::count(),
                'thisMonth' => Vital::whereYear('recorded_at', $currentYear)
                    ->whereMonth('recorded_at', $currentMonth)
                    ->count(),
            ],
        ];

        // Billing Analytics
        $billingAnalytics = $this->getBillingAnalytics($currentMonth, $currentYear, $lastMonth);

        // Trend Analysis (Last 6 months)
        $trends = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $trends[] = [
                'month' => $date->format('M Y'),
                'appointments' => Appointment::whereYear('date', $date->year)
                    ->whereMonth('date', $date->month)
                    ->count(),
                'visits' => Visit::whereYear('date', $date->year)
                    ->whereMonth('date', $date->month)
                    ->count(),
                'newPatients' => User::where('role', 'patient')
                    ->whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->count(),
            ];
        }

        // Billing Trends (Last 6 months)
        $billingTrends = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthBills = Billing::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month);
            
            $monthRevenue = $monthBills->sum('amount');
            $monthBillsCount = $monthBills->count();
            $monthPaidCount = Billing::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->where('status', 'paid')
                ->count();
            
            $collectionRate = $monthBillsCount > 0 ? round(($monthPaidCount / $monthBillsCount) * 100, 1) : 0;
            
            $billingTrends[] = [
                'month' => $date->format('M Y'),
                'revenue' => $monthRevenue,
                'bills' => $monthBillsCount,
                'paid' => $monthPaidCount,
                'collectionRate' => $collectionRate,
            ];
        }

        // Top Paying Patients
        $topPayingPatients = Billing::select('patient_id', DB::raw('SUM(amount) as total_paid'), DB::raw('COUNT(*) as bills'))
            ->where('status', 'paid')
            ->with('patient:id,name')
            ->groupBy('patient_id')
            ->orderByDesc('total_paid')
            ->limit(5)
            ->get()
            ->map(function ($billing) {
                return [
                    'name' => $billing->patient->name ?? 'Unknown Patient',
                    'totalPaid' => $billing->total_paid,
                    'bills' => $billing->bills,
                ];
            });

        // Top Revenue Services
        $topRevenueServices = BillingItem::select('description', DB::raw('SUM(total) as total_revenue'), DB::raw('COUNT(*) as count'))
            ->groupBy('description')
            ->orderByDesc('total_revenue')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return [
                    'description' => $item->description,
                    'totalRevenue' => $item->total_revenue,
                    'count' => $item->count,
                ];
            });

        // Top Performing Metrics
        $topMetrics = [
            'busiestDay' => Appointment::select('date', DB::raw('count(*) as count'))
                ->whereYear('date', $currentYear)
                ->whereMonth('date', $currentMonth)
                ->groupBy('date')
                ->orderByDesc('count')
                ->first(),
            'mostActiveDoctor' => Appointment::select('doctor_id', DB::raw('count(*) as count'))
                ->whereNotNull('doctor_id')
                ->whereYear('date', $currentYear)
                ->whereMonth('date', $currentMonth)
                ->groupBy('doctor_id')
                ->orderByDesc('count')
                ->with('doctor')
                ->first(),
        ];

        return [
            'patientDemographics' => $patientDemographics,
            'appointmentAnalytics' => $appointmentAnalytics,
            'staffPerformance' => $staffPerformance,
            'medicalRecordsAnalytics' => $medicalRecordsAnalytics,
            'billingAnalytics' => $billingAnalytics,
            'billingTrends' => $billingTrends,
            'topPayingPatients' => $topPayingPatients,
            'topRevenueServices' => $topRevenueServices,
            'trends' => $trends,
            'topMetrics' => $topMetrics,
        ];
    }

    private function getBillingAnalytics($currentMonth, $currentYear, $lastMonth)
    {
        // Total revenue and bills
        $totalRevenue = Billing::sum('amount');
        $totalBills = Billing::count();
        
        // Current month revenue
        $revenueThisMonth = Billing::whereYear('created_at', $currentYear)
            ->whereMonth('created_at', $currentMonth)
            ->sum('amount');
        
        // Last month revenue
        $revenueLastMonth = Billing::whereYear('created_at', $lastMonth->year)
            ->whereMonth('created_at', $lastMonth->month)
            ->sum('amount');
        
        // Current month revenue for comparison
        $currentMonthRevenue = $revenueThisMonth;
        $lastMonthRevenue = $revenueLastMonth;
        
        // Growth rate calculation
        $growthRate = $lastMonthRevenue > 0 ? round((($currentMonthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100, 1) : 0;
        
        // Billing status breakdown
        $paidBills = Billing::where('status', 'paid')->count();
        $pendingBills = Billing::where('status', 'pending')->count();
        $overdueBills = Billing::where('status', 'overdue')->count();
        
        // Amounts by status
        $paidAmount = Billing::where('status', 'paid')->sum('amount');
        $pendingAmount = Billing::where('status', 'pending')->sum('amount');
        $overdueAmount = Billing::where('status', 'overdue')->sum('amount');
        
        // Collection rate
        $collectionRate = $totalBills > 0 ? round(($paidBills / $totalBills) * 100, 1) : 0;
        
        // Average bill amount
        $averageBillAmount = $totalBills > 0 ? round($totalRevenue / $totalBills, 0) : 0;
        
        return [
            'totalRevenue' => $totalRevenue,
            'revenueThisMonth' => $revenueThisMonth,
            'totalBills' => $totalBills,
            'collectionRate' => $collectionRate,
            'paidBills' => $paidBills,
            'pendingBills' => $pendingBills,
            'overdueBills' => $overdueBills,
            'paidAmount' => $paidAmount,
            'pendingAmount' => $pendingAmount,
            'overdueAmount' => $overdueAmount,
            'averageBillAmount' => $averageBillAmount,
            'currentMonthRevenue' => $currentMonthRevenue,
            'lastMonthRevenue' => $lastMonthRevenue,
            'growthRate' => $growthRate,
        ];
    }
} 