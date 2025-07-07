<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\VitalController;
use App\Http\Controllers\LabResultController;
use App\Http\Controllers\PrescriptionController;
use App\Http\Controllers\VisitController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\BillingController;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', \App\Http\Controllers\DashboardController::class)
    ->middleware(['auth', 'verified'])->name('dashboard');

Route::get('admin/dashboard', \App\Http\Controllers\AdminDashboardController::class)
    ->middleware(['auth', 'verified'])->name('admin.dashboard');

Route::middleware(['auth'])->group(function () {
    Route::get('/appointments', function () {
        return Inertia::render('appointments');
    });
    Route::get('/vitals', [VitalController::class, 'index'])->name('vitals.index');
    Route::get('/lab-results', [LabResultController::class, 'index'])->name('lab-results.index');
    Route::get('/prescriptions', [PrescriptionController::class, 'index'])->name('prescriptions.index');
    Route::get('/visits', [VisitController::class, 'index'])->name('visits.index');
    // Admin-only routes
    Route::get('/admin/prescriptions', [\App\Http\Controllers\PrescriptionController::class, 'adminIndex'])->name('admin.prescriptions');
    Route::post('/admin/prescriptions/{id}/update', [\App\Http\Controllers\PrescriptionController::class, 'update'])->name('admin.prescriptions.update');
    Route::delete('/admin/prescriptions/{id}', [\App\Http\Controllers\PrescriptionController::class, 'destroy'])->name('admin.prescriptions.destroy');
    Route::post('/admin/prescriptions/{id}/assign', [\App\Http\Controllers\PrescriptionController::class, 'assign'])->name('admin.prescriptions.assign');
    Route::get('/admin/prescriptions-export', [\App\Http\Controllers\PrescriptionController::class, 'export'])->name('admin.prescriptions.export');
    Route::get('/admin/lab-results', [\App\Http\Controllers\LabResultController::class, 'adminIndex'])->name('admin.lab-results');
    Route::post('/admin/lab-results/{id}/update', [\App\Http\Controllers\LabResultController::class, 'update'])->name('admin.lab-results.update');
    Route::delete('/admin/lab-results/{id}', [\App\Http\Controllers\LabResultController::class, 'destroy'])->name('admin.lab-results.destroy');
    Route::post('/admin/lab-results/{id}/assign', [\App\Http\Controllers\LabResultController::class, 'assign'])->name('admin.lab-results.assign');
    Route::get('/admin/lab-results-export', [\App\Http\Controllers\LabResultController::class, 'export'])->name('admin.lab-results.export');
    Route::get('/admin/vitals', [\App\Http\Controllers\VitalController::class, 'adminIndex'])->name('admin.vitals');
    Route::get('/admin/visits', [\App\Http\Controllers\VisitController::class, 'adminIndex'])->name('admin.visits');
    Route::post('/admin/vitals/{id}/update', [\App\Http\Controllers\VitalController::class, 'update'])->name('admin.vitals.update');
    Route::delete('/admin/vitals/{id}', [\App\Http\Controllers\VitalController::class, 'destroy'])->name('admin.vitals.destroy');
    Route::post('/admin/vitals/{id}/assign', [\App\Http\Controllers\VitalController::class, 'assign'])->name('admin.vitals.assign');
    Route::get('/admin/vitals-export', [\App\Http\Controllers\VitalController::class, 'export'])->name('admin.vitals.export');
    Route::post('/admin/visits/{id}/update', [\App\Http\Controllers\VisitController::class, 'update'])->name('admin.visits.update');
    Route::delete('/admin/visits/{id}', [\App\Http\Controllers\VisitController::class, 'destroy'])->name('admin.visits.destroy');
    Route::post('/admin/visits/{id}/assign', [\App\Http\Controllers\VisitController::class, 'assign'])->name('admin.visits.assign');
    Route::get('/admin/visits-export', [\App\Http\Controllers\VisitController::class, 'export'])->name('admin.visits.export');
    
    // Admin Appointment Management Routes
    Route::get('/admin/appointments', [\App\Http\Controllers\AppointmentController::class, 'adminIndex'])->name('admin.appointments');
    Route::post('/admin/appointments/{appointment}/accept', [\App\Http\Controllers\AppointmentController::class, 'accept'])->name('admin.appointments.accept');
    Route::post('/admin/appointments/{appointment}/cancel', [\App\Http\Controllers\AppointmentController::class, 'adminCancel'])->name('admin.appointments.cancel');
    Route::post('/admin/appointments/{appointment}/assign', [\App\Http\Controllers\AppointmentController::class, 'assign'])->name('admin.appointments.assign');
    Route::get('/admin/appointments/staff', [\App\Http\Controllers\AppointmentController::class, 'staffList'])->name('admin.appointments.staff');
    
    Route::get('/admin/activity-logs', [\App\Http\Controllers\ActivityLogController::class, 'index'])->name('admin.activity-logs');
    // Route::get('/add-staff', function () {
    //     return Inertia::render('AddStaff');
    // })->name('add-staff');
    Route::get('/patient-management', function () {
        return Inertia::render('PatientManagement');
    })->name('patient-management');
    Route::get('/staff-management', function () {
        return Inertia::render('StaffManagement');
    })->name('staff-management');
    Route::get('/my-billings', [\App\Http\Controllers\BillingController::class, 'myBills'])->name('my.billings');
    Route::get('/my-billings/{billing}', [\App\Http\Controllers\BillingController::class, 'show'])->name('my.billings.show');
    Route::get('/my-billings/{billing}/pdf', [\App\Http\Controllers\BillingController::class, 'pdf'])->name('my.billings.pdf');
});

// Billing & Invoicing
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/billings', [\App\Http\Controllers\BillingController::class, 'index'])->name('admin.billings');
    Route::get('/admin/billings/create', [\App\Http\Controllers\BillingController::class, 'create'])->name('admin.billings.create');
    Route::post('/admin/billings', [\App\Http\Controllers\BillingController::class, 'store'])->name('admin.billings.store');
    Route::get('/admin/billings/{billing}/edit', [\App\Http\Controllers\BillingController::class, 'edit'])->name('admin.billings.edit');
    Route::put('/admin/billings/{billing}', [\App\Http\Controllers\BillingController::class, 'update'])->name('admin.billings.update');
    Route::delete('/admin/billings/{billing}', [\App\Http\Controllers\BillingController::class, 'destroy'])->name('admin.billings.destroy');
    Route::post('/admin/billings/{billing}/mark-paid', [\App\Http\Controllers\BillingController::class, 'markAsPaid'])->name('admin.billings.markPaid');
    Route::get('/admin/billings/{billing}', [\App\Http\Controllers\BillingController::class, 'show'])->name('admin.billings.show');
    Route::get('/admin/billings/{billing}/pdf', [\App\Http\Controllers\BillingController::class, 'pdf'])->name('admin.billings.pdf');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
