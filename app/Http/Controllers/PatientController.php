<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PatientController extends Controller
{
    /**
     * Display a listing of patients (admin only)
     */
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user || $user->role !== 'admin') {
            abort(403, 'Only admins can view all patients.');
        }

        $perPage = $request->get('per_page', 15);
        $search = $request->get('search', '');

        $query = User::where('role', 'patient');

        // Apply search filter if provided
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $patients = $query->orderBy('created_at', 'desc')
            ->paginate($perPage, ['id', 'name', 'email', 'phone', 'email_verified_at', 'created_at']);

        return response()->json($patients);
    }

    /**
     * Store a newly created patient
     */
    public function store(Request $request)
    {
        $user = $request->user();
        if (!$user || $user->role !== 'admin') {
            abort(403, 'Only admins can create patients.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|max:20',
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $patient = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'role' => 'patient',
            'email_verified_at' => now(),
        ]);

        return response()->json($patient, 201);
    }

    /**
     * Display the specified patient
     */
    public function show(Request $request, User $patient)
    {
        $user = $request->user();
        if (!$user || $user->role !== 'admin') {
            abort(403, 'Only admins can view patient details.');
        }

        if ($patient->role !== 'patient') {
            abort(404, 'Patient not found.');
        }

        // Load patient's medical data
        $patient->load([
            'appointments.doctor',
            'labResults',
            'prescriptions',
            'visits.doctor',
            'vitals'
        ]);

        return response()->json($patient);
    }

    /**
     * Update the specified patient
     */
    public function update(Request $request, User $patient)
    {
        $user = $request->user();
        if (!$user || $user->role !== 'admin') {
            abort(403, 'Only admins can update patients.');
        }

        if ($patient->role !== 'patient') {
            abort(404, 'Patient not found.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $patient->id,
            'phone' => 'required|string|max:20',
        ]);

        $patient->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
        ]);

        return response()->json($patient);
    }

    /**
     * Remove the specified patient
     */
    public function destroy(Request $request, User $patient)
    {
        $user = $request->user();
        if (!$user || $user->role !== 'admin') {
            abort(403, 'Only admins can delete patients.');
        }

        if ($patient->role !== 'patient') {
            abort(404, 'Patient not found.');
        }

        // Check if patient has any medical records
        $hasRecords = $patient->appointments()->exists() ||
                     $patient->labResults()->exists() ||
                     $patient->prescriptions()->exists() ||
                     $patient->visits()->exists() ||
                     $patient->vitals()->exists();

        if ($hasRecords) {
            return response()->json([
                'error' => 'Cannot delete patient with existing medical records.'
            ], 422);
        }

        $patient->delete();
        return response()->json(['success' => true]);
    }
} 