<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\VitalController;
use App\Http\Controllers\LabResultController;
use App\Http\Controllers\PrescriptionController;
use App\Http\Controllers\VisitController;
use App\Http\Controllers\DashboardController;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', \App\Http\Controllers\DashboardController::class)
    ->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::get('/appointments', function () {
        return Inertia::render('appointments');
    });
    Route::get('/vitals', [VitalController::class, 'index'])->name('vitals.index');
    Route::get('/lab-results', [LabResultController::class, 'index'])->name('lab-results.index');
    Route::get('/prescriptions', [PrescriptionController::class, 'index'])->name('prescriptions.index');
    Route::get('/visits', [VisitController::class, 'index'])->name('visits.index');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
