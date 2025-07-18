<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\StaffController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Api\MessageController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Staff API routes
Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/staff/appointments', function (Request $request) {
        $user = $request->user();
        
        if (!in_array($user->role, ['doctor', 'nurse'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $appointments = \App\Models\Appointment::where('doctor_id', $user->id)
            ->with('patient')
            ->orderBy('date', 'desc')
            ->orderBy('time', 'desc')
            ->get();
            
        return response()->json(['appointments' => $appointments]);
    });
    
    Route::get('/staff/patients', [\App\Http\Controllers\PatientController::class, 'myPatients']);
    
    Route::get('/staff/prescriptions', function (Request $request) {
        $user = $request->user();
        
        if (!in_array($user->role, ['doctor', 'nurse'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $prescriptions = \App\Models\Prescription::where('doctor', $user->name)
            ->with(['patient', 'drug'])
            ->orderBy('created_at', 'desc')
            ->get();
            
        return response()->json(['prescriptions' => $prescriptions]);
    });
    
    Route::get('/staff/lab-results', function (Request $request) {
        $user = $request->user();
        
        if (!in_array($user->role, ['doctor', 'nurse'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $labResults = \App\Models\LabResult::where('doctor', $user->name)
            ->with('patient')
            ->orderBy('date', 'desc')
            ->get();
            
        return response()->json(['labResults' => $labResults]);
    });
    
    Route::get('/staff/vitals', function (Request $request) {
        $user = $request->user();
        
        if (!in_array($user->role, ['doctor', 'nurse'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $vitals = \App\Models\Vital::where('recorded_by', $user->name)
            ->with('patient')
            ->orderBy('recorded_at', 'desc')
            ->get();
            
        return response()->json(['vitals' => $vitals]);
    });
    
    Route::get('/staff/visits', function (Request $request) {
        $user = $request->user();
        
        if (!in_array($user->role, ['doctor', 'nurse'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $visits = \App\Models\Visit::where('doctor_id', $user->id)
            ->with('patient')
            ->orderBy('date', 'desc')
            ->get();
            
        return response()->json(['visits' => $visits]);
    });

    Route::get('/staff/drugs', function () {
        $user = auth()->user();
        if (!in_array($user->role, ['doctor', 'nurse'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $drugs = \App\Models\Drug::orderBy('name')->get(['id', 'name', 'generic_name', 'strength', 'dosage_form']);
        return response()->json(['drugs' => $drugs]);
    });

    Route::get('/staff/patients/{id}/profile', function ($id) {
        $user = auth()->user();
        if (!in_array($user->role, ['doctor', 'nurse'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $patient = \App\Models\User::findOrFail($id);
        $vitals = \App\Models\Vital::where('patient_id', $id)->orderByDesc('recorded_at')->get();
        $prescriptions = \App\Models\Prescription::where('patient_id', $id)->with('drug')->orderByDesc('start_date')->get();
        $labResults = \App\Models\LabResult::where('patient_id', $id)->orderByDesc('date')->get();
        // Timeline: merge all actions, sort by date desc
        $timeline = collect([]);
        foreach ($vitals as $v) {
            $timeline->push([
                'id' => 'vital-' . $v->id,
                'date' => $v->recorded_at,
                'title' => 'Vitals Recorded',
                'description' => 'Vitals recorded: ' . ($v->temperature ? 'Temp ' . $v->temperature . '°C, ' : '') . ($v->blood_pressure ? 'BP ' . $v->blood_pressure . ', ' : '') . ($v->heart_rate ? 'HR ' . $v->heart_rate : ''),
            ]);
        }
        foreach ($prescriptions as $rx) {
            $timeline->push([
                'id' => 'rx-' . $rx->id,
                'date' => $rx->start_date,
                'title' => 'Prescription',
                'description' => 'Prescribed ' . ($rx->drug->name ?? $rx->drug->generic_name ?? 'Medication') . ' (' . $rx->dosage . ')',
            ]);
        }
        foreach ($labResults as $lab) {
            $timeline->push([
                'id' => 'lab-' . $lab->id,
                'date' => $lab->date,
                'title' => 'Lab Result',
                'description' => $lab->type . ': ' . $lab->result,
            ]);
        }
        $timeline = $timeline->sortByDesc('date')->values();
        return response()->json([
            'patient' => $patient,
            'vitals' => $vitals,
            'prescriptions' => $prescriptions,
            'labResults' => $labResults,
            'timeline' => $timeline,
        ]);
    });

    Route::post('/staff/patients/{id}/vitals', function ($id) {
        $user = auth()->user();
        if (!in_array($user->role, ['doctor', 'nurse'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $data = request()->validate([
            'temperature' => 'nullable|numeric',
            'blood_pressure' => 'nullable|string',
            'heart_rate' => 'nullable|integer',
            'weight' => 'nullable|numeric',
            'recorded_at' => 'required|date',
        ]);
        $data['patient_id'] = $id;
        $data['recorded_by'] = $user->name;
        $vital = \App\Models\Vital::create($data);
        return response()->json(['success' => true, 'vital' => $vital]);
    });
    Route::post('/staff/patients/{id}/prescriptions', function ($id) {
        $user = auth()->user();
        if (!in_array($user->role, ['doctor', 'nurse'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $data = request()->validate([
            'drug_id' => 'required|integer|exists:drugs,id',
            'dosage' => 'required|string',
            'instructions' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);
        $data['patient_id'] = $id;
        $data['doctor'] = $user->name;
        $prescription = \App\Models\Prescription::create($data);
        return response()->json(['success' => true, 'prescription' => $prescription]);
    });
    Route::post('/staff/patients/{id}/lab-results', function ($id) {
        $user = auth()->user();
        if (!in_array($user->role, ['doctor', 'nurse'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $data = request()->validate([
            'type' => 'required|string',
            'result' => 'nullable|string',
            'notes' => 'nullable|string',
            'date' => 'required|date',
        ]);
        $data['patient_id'] = $id;
        $data['doctor'] = $user->name;
        $lab = \App\Models\LabResult::create($data);
        return response()->json(['success' => true, 'labResult' => $lab]);
    });
});

Route::middleware(['web', 'auth'])->group(function () {
    // Appointment routes
    Route::get('/appointments', [AppointmentController::class, 'index']);
    Route::post('/appointments', [AppointmentController::class, 'store']);
    Route::put('/appointments/{appointment}', [AppointmentController::class, 'update']);
    Route::post('/appointments/{appointment}/cancel', [AppointmentController::class, 'cancel']);
    Route::get('/appointments', [AppointmentController::class, 'unifiedIndex']);
    Route::post('/appointments/{appointment}/accept', [AppointmentController::class, 'accept']);
    Route::post('/appointments/{appointment}/admin-cancel', [AppointmentController::class, 'adminCancel']);
    Route::post('/appointments/{appointment}/assign', [AppointmentController::class, 'assign']);
    Route::get('/appointment-staff', [AppointmentController::class, 'staffList']);

    // Patient management routes (admin only)
    Route::get('/patients', [PatientController::class, 'index']);
    Route::post('/patients', [PatientController::class, 'store']);
    Route::get('/patients/{patient}', [PatientController::class, 'show']);
    Route::put('/patients/{patient}', [PatientController::class, 'update']);
    Route::delete('/patients/{patient}', [PatientController::class, 'destroy']);
    Route::post('/admin/patients/{id}/assign-doctor', function ($id) {
        $user = auth()->user();
        \Log::info('Assign staff endpoint hit', ['admin_id' => $user->id, 'patient_id' => $id, 'request' => request()->all()]);
        if (!$user || $user->role !== 'admin') {
            return response()->json(['message' => 'Only admins can assign staff.'], 403);
        }
        $data = request()->validate([
            'doctor_id' => 'required|exists:users,id',
        ]);
        $patient = \App\Models\User::where('id', $id)->where('role', 'patient')->firstOrFail();
        $staff = \App\Models\User::where('id', $data['doctor_id'])->whereIn('role', ['doctor', 'nurse'])->first();
        if (!$staff) {
            return response()->json(['message' => 'Selected user is not a doctor or nurse.'], 422);
        }
        // Prevent duplicate assignment
        if ($patient->patientDoctors()->where('users.id', $staff->id)->exists()) {
            return response()->json(['message' => 'Staff already assigned to this patient.'], 422);
        }
        \Log::info('Attaching staff to patient', ['patient_id' => $patient->id, 'staff_id' => $staff->id, 'role' => $staff->role]);
        $patient->patientDoctors()->attach($staff->id, [
            'role' => $staff->role,
            'assigned_at' => now(),
        ]);
        // dd('Attached', $patient->id, $staff->id, $staff->role);
        $assignedStaff = $patient->patientDoctors()->get();
        \Log::info('Assigned staff after attach', ['assigned_staff' => $assignedStaff]);
        return response()->json(['message' => 'Staff assigned successfully.', 'assigned_staff' => $assignedStaff]);
    });
    Route::delete('/admin/patients/{patient}/unassign-doctor/{doctor}', [PatientController::class, 'unassignDoctor']);

    // Staff management routes (admin only)
    Route::get('/staff', [StaffController::class, 'index']);
    Route::post('/staff', [StaffController::class, 'store']);
    Route::get('/staff/{staff}', [StaffController::class, 'show']);
    Route::put('/staff/{staff}', [StaffController::class, 'update']);
    Route::delete('/staff/{staff}', [StaffController::class, 'destroy']);
    Route::get('/staff-statistics', [StaffController::class, 'statistics']);
    Route::get('/staff-list', [StaffController::class, 'list']);

    // Patient visits API endpoint
    Route::get('/visits', function (Request $request) {
        $user = $request->user();
        if ($user->role !== 'patient') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $visits = \App\Models\Visit::where('patient_id', $user->id)
            ->with(['patient', 'doctor'])
            ->orderBy('date', 'desc')
            ->get();
        return response()->json(['visits' => $visits]);
    });

    // Patient vitals API endpoint
    Route::get('/vitals', function (Request $request) {
        $user = $request->user();
        if ($user->role !== 'patient') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $vitals = \App\Models\Vital::where('patient_id', $user->id)
            ->orderBy('recorded_at', 'desc')
            ->get();
        return response()->json(['vitals' => $vitals]);
    });

    // Patient lab results API endpoint
    Route::get('/lab-results', function (Request $request) {
        $user = $request->user();
        if ($user->role !== 'patient') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $labResults = \App\Models\LabResult::where('patient_id', $user->id)
            ->orderBy('date', 'desc')
            ->get();
        return response()->json(['labResults' => $labResults]);
    });
});

Route::middleware(['web', 'auth:sanctum'])->group(function () {
    Route::post('/messages/send', [MessageController::class, 'send']);
    Route::get('/messages/poll', [MessageController::class, 'poll']);
    Route::post('/messages/mark-read', [MessageController::class, 'markAsRead']);
});

Route::middleware(['web', 'auth'])->get('/notifications', function (Request $request) {
    return $request->user()->notifications()->limit(20)->get();
});
Route::middleware(['web', 'auth'])->post('/notifications/read', function (Request $request) {
    $request->user()->unreadNotifications->markAsRead();
    return response()->noContent();
});