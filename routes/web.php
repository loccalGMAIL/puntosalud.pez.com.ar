<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfessionalController;
use App\Http\Controllers\ProfessionalScheduleController;
use App\Http\Controllers\SpecialtyController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\AgendaController;

Route::get('/', function () {
    return view('dashboard');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

Route::resource('professionals', ProfessionalController::class);
Route::patch('/professionals/{professional}/toggle-status', [ProfessionalController::class, 'toggleStatus'])->name('professionals.toggle-status');

Route::get('/professionals/{professional}/schedules', [ProfessionalScheduleController::class, 'index'])->name('professionals.schedules.index');
Route::post('/professionals/{professional}/schedules', [ProfessionalScheduleController::class, 'store'])->name('professionals.schedules.store');
Route::put('/professionals/{professional}/schedules/{schedule}', [ProfessionalScheduleController::class, 'update'])->name('professionals.schedules.update');
Route::delete('/professionals/{professional}/schedules/{schedule}', [ProfessionalScheduleController::class, 'destroy'])->name('professionals.schedules.destroy');

Route::resource('specialties', SpecialtyController::class)->only(['index', 'store', 'update', 'destroy']);

Route::resource('patients', PatientController::class);

Route::resource('appointments', AppointmentController::class);
Route::get('/appointments/available-slots', [AppointmentController::class, 'availableSlots'])->name('appointments.available-slots');

Route::get('/agenda', [AgendaController::class, 'index'])->name('agenda.index');
