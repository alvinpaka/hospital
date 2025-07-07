<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\StaffController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

    // Staff management routes (admin only)
    Route::get('/staff', [StaffController::class, 'index']);
    Route::post('/staff', [StaffController::class, 'store']);
    Route::get('/staff/{staff}', [StaffController::class, 'show']);
    Route::put('/staff/{staff}', [StaffController::class, 'update']);
    Route::delete('/staff/{staff}', [StaffController::class, 'destroy']);
    Route::get('/staff-statistics', [StaffController::class, 'statistics']);
    Route::get('/staff-list', [StaffController::class, 'list']);
});

Route::middleware(['auth:sanctum'])->get('/notifications', function (Request $request) {
    return $request->user()->notifications()->limit(20)->get();
});
Route::middleware(['auth:sanctum'])->post('/notifications/read', function (Request $request) {
    $request->user()->unreadNotifications->markAsRead();
    return response()->noContent();
});