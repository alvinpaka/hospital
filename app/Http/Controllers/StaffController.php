<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class StaffController extends Controller
{
    /**
     * Display a listing of staff members.
     */
    public function index(Request $request)
    {
        $query = User::query();

        // Exclude patients
        $query->where('role', '!=', 'patient');

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('role', 'like', "%{$search}%");
            });
        }

        // Filter by role if specified
        if ($request->has('role') && $request->role) {
            $query->where('role', $request->role);
        }

        // Pagination
        $perPage = $request->get('per_page', 10);
        $staff = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json($staff);
    }

    /**
     * Store a newly created staff member.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|max:20',
            'role' => 'required|string|in:doctor,nurse,admin',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $staff = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'role' => $request->role,
                'password' => Hash::make($request->password),
                'email_verified_at' => now(), // Auto-verify staff accounts
            ]);

            return response()->json([
                'message' => 'Staff member created successfully',
                'staff' => $staff
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create staff member',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified staff member.
     */
    public function show(User $staff)
    {
        // Load related data for detailed view
        $staff->load([
            'appointments' => function ($query) {
                $query->latest()->limit(10);
            },
            'appointments.patient',
            'prescriptions' => function ($query) {
                $query->latest()->limit(10);
            },
            'visits' => function ($query) {
                $query->latest()->limit(10);
            }
        ]);

        return response()->json($staff);
    }

    /**
     * Update the specified staff member.
     */
    public function update(Request $request, User $staff)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($staff->id),
            ],
            'phone' => 'required|string|max:20',
            'role' => 'required|string|in:doctor,nurse,admin',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $staff->update([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'role' => $request->role,
            ]);

            return response()->json([
                'message' => 'Staff member updated successfully',
                'staff' => $staff
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update staff member',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified staff member.
     */
    public function destroy(User $staff)
    {
        // Prevent deleting the last admin
        if ($staff->role === 'admin') {
            $adminCount = User::where('role', 'admin')->count();
            if ($adminCount <= 1) {
                return response()->json([
                    'message' => 'Cannot delete the last administrator'
                ], 422);
            }
        }

        // Prevent deleting self
        if ($staff->id === auth()->id()) {
            return response()->json([
                'message' => 'Cannot delete your own account'
            ], 422);
        }

        try {
            $staff->delete();
            return response()->json([
                'message' => 'Staff member deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete staff member',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get staff statistics for dashboard.
     */
    public function statistics()
    {
        $stats = [
            'total_staff' => User::count(),
            'doctors' => User::where('role', 'doctor')->count(),
            'nurses' => User::where('role', 'nurse')->count(),
            'admins' => User::where('role', 'admin')->count(),
            'verified_staff' => User::whereNotNull('email_verified_at')->count(),
            'unverified_staff' => User::whereNull('email_verified_at')->count(),
        ];

        return response()->json($stats);
    }

    /**
     * Get staff list for dropdowns (simplified version).
     */
    public function list()
    {
        $staff = User::select('id', 'name', 'role')
            ->whereIn('role', ['doctor', 'nurse', 'admin'])
            ->orderBy('name')
            ->get();

        return response()->json($staff);
    }
} 