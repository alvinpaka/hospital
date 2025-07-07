<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Notifications\AppointmentAcceptedNotification;
use App\Notifications\AppointmentCanceledNotification;
use App\Notifications\NewAppointmentNotification;
use App\Models\User;
use Inertia\Inertia;

class AppointmentController extends Controller
{
    public function index()
    {
        $appointments = Appointment::with('doctor')
            ->where('patient_id', Auth::id())
            ->orderBy('date', 'desc')
            ->orderBy('time', 'desc')
            ->get();

        return response()->json($appointments);
    }

    public function store(Request $request)
    {
        $request->validate([
            'doctor_id' => 'nullable|exists:users,id',
            'date' => 'required|date',
            'time' => 'required',
            'notes' => 'nullable|string',
        ]);

        $appointment = Appointment::create([
            'patient_id' => Auth::id(),
            'doctor_id' => $request->doctor_id,
            'date' => $request->date,
            'time' => $request->time,
            'status' => 'pending',
            'notes' => $request->notes,
        ]);

        // Notify all admins
        $admins = User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            $admin->notify(new NewAppointmentNotification());
        }

        return response()->json($appointment, 201);
    }

    public function update(Request $request, Appointment $appointment)
    {
        if ($appointment->patient_id !== Auth::id()) {
            abort(403);
        }
        $request->validate([
            'date' => 'required|date',
            'time' => 'required',
            'notes' => 'nullable|string',
        ]);
        $appointment->update([
            'date' => $request->date,
            'time' => $request->time,
            'notes' => $request->notes,
        ]);
        return response()->json($appointment);
    }

    public function cancel(Appointment $appointment)
    {
        if ($appointment->patient_id !== Auth::id()) {
            abort(403);
        }
        $data = [
            'status' => 'canceled',
        ];
        if (request()->has('cancellation_reason')) {
            $data['cancellation_reason'] = request('cancellation_reason');
        }
        $appointment->update($data);
        return response()->json(['success' => true]);
    }

    /**
     * Admin: List all appointments with patient and doctor info.
     */
    public function adminIndex(Request $request)
    {
        $user = $request->user();
        if (!$user || $user->role !== 'admin') {
            abort(403, 'Only admins can view all appointments.');
        }
        
        $appointments = Appointment::with(['doctor', 'patient'])
            ->orderBy('date', 'desc')
            ->orderBy('time', 'desc')
            ->get();
            
        $staff = User::whereIn('role', ['doctor', 'nurse'])->get(['id', 'name', 'role']);
        
        return Inertia::render('AdminAppointments', [
            'appointments' => $appointments,
            'staff' => $staff,
        ]);
    }

    /**
     * Unified: List appointments for admin (all) or patient (own).
     */
    public function unifiedIndex(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            abort(403, 'Unauthorized');
        }
        if ($user->role === 'admin') {
            $appointments = Appointment::with(['doctor', 'patient'])
                ->orderBy('date', 'desc')
                ->orderBy('time', 'desc')
                ->get();
        } elseif ($user->role === 'doctor' || $user->role === 'nurse') {
            $appointments = Appointment::with(['doctor', 'patient'])
                ->where('doctor_id', $user->id)
                ->orderBy('date', 'desc')
                ->orderBy('time', 'desc')
                ->get();
        } else {
            $appointments = Appointment::with(['doctor', 'patient'])
                ->where('patient_id', $user->id)
                ->orderBy('date', 'desc')
                ->orderBy('time', 'desc')
                ->get();
        }
        return response()->json($appointments);
    }

    public function accept(Request $request, Appointment $appointment)
    {
        $user = $request->user();
        if (!$user || $user->role !== 'admin') abort(403);
        $appointment->update(['status' => 'confirmed']);

        // Notify the patient
        if ($appointment->patient) {
            $appointment->patient->notify(new AppointmentAcceptedNotification());
        }

        return redirect()->back()->with('success', 'Appointment accepted successfully.');
    }

    public function adminCancel(Request $request, Appointment $appointment)
    {
        $user = $request->user();
        if (!$user || $user->role !== 'admin') abort(403);
        $appointment->update([
            'status' => 'canceled',
            'cancellation_reason' => $request->cancellation_reason,
        ]);

        // Notify the patient
        if ($appointment->patient) {
            $appointment->patient->notify(new AppointmentCanceledNotification());
        }

        return redirect()->back()->with('success', 'Appointment canceled successfully.');
    }

    public function assign(Request $request, Appointment $appointment)
    {
        $user = $request->user();
        if (!$user || $user->role !== 'admin') abort(403);
        $request->validate([
            'doctor_id' => 'required|exists:users,id',
        ]);
        $appointment->update(['doctor_id' => $request->doctor_id]);
        return redirect()->back()->with('success', 'Doctor assigned successfully.');
    }

    public function staffList(Request $request)
    {
        $user = $request->user();
        if (!$user || $user->role !== 'admin') abort(403);
        $staff = \App\Models\User::whereIn('role', ['doctor', 'nurse'])->get(['id', 'name', 'role']);
        return response()->json($staff);
    }
} 