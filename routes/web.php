<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfessionalController;
use App\Http\Controllers\ProfessionalScheduleController;
use App\Http\Controllers\SpecialtyController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\AgendaController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;

// Authentication routes
Route::get('/', function() {
    return redirect()->route('login');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected routes
Route::middleware(['auth'])->group(function () {

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// Dashboard appointment status routes
Route::post('/dashboard/appointments/{appointment}/mark-attended', [DashboardController::class, 'markAttended'])->name('dashboard.mark-attended');
Route::post('/dashboard/appointments/{appointment}/mark-completed-paid', [DashboardController::class, 'markCompletedAndPaid'])->name('dashboard.mark-completed-paid');
Route::post('/dashboard/appointments/{appointment}/mark-absent', [DashboardController::class, 'markAbsent'])->name('dashboard.mark-absent');

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

Route::resource('payments', PaymentController::class);
Route::get('/payments/search-patients', [PaymentController::class, 'searchPatients'])->name('payments.search-patients');
Route::get('/payments/patients/{patient}/pending-appointments', [PaymentController::class, 'getPendingAppointments'])->name('payments.pending-appointments');

// Payment allocation routes
Route::post('/payments/{payment}/use-session', [PaymentController::class, 'usePackageSession'])->name('payments.use-session');
Route::post('/payments/{payment}/allocate-single', [PaymentController::class, 'allocateSinglePayment'])->name('payments.allocate-single');
Route::delete('/payment-appointments/{paymentAppointment}', [PaymentController::class, 'deallocatePayment'])->name('payments.deallocate');
Route::get('/patients/{patient}/available-packages', [PaymentController::class, 'getAvailablePackages'])->name('patients.available-packages');
Route::get('/payments/{payment}/allocation-summary', [PaymentController::class, 'getPaymentAllocationSummary'])->name('payments.allocation-summary');
Route::post('/appointments/{appointment}/auto-allocate', [PaymentController::class, 'autoAllocatePayment'])->name('appointments.auto-allocate');

// Reports routes
Route::get('/reports/daily-schedule', [ReportController::class, 'dailySchedule'])->name('reports.daily-schedule');
Route::get('/reports/daily-summary', [ReportController::class, 'dailySummary'])->name('reports.daily-summary');
Route::get('/reports/professional-liquidation', [ReportController::class, 'professionalLiquidation'])->name('reports.professional-liquidation');

// Cash management routes
Route::get('/cash/daily', [App\Http\Controllers\CashController::class, 'dailyCash'])->name('cash.daily');
Route::get('/cash/daily-report', [App\Http\Controllers\CashController::class, 'dailyReport'])->name('cash.daily-report');
Route::get('/cash/report', [App\Http\Controllers\CashController::class, 'cashReport'])->name('cash.report');
Route::get('/cash/expense', [App\Http\Controllers\CashController::class, 'addExpense'])->name('cash.expense-form');
Route::post('/cash/expense', [App\Http\Controllers\CashController::class, 'addExpense'])->name('cash.expense.store');
Route::get('/cash/withdrawal', [App\Http\Controllers\CashController::class, 'withdrawalForm'])->name('cash.withdrawal-form');
Route::post('/cash/withdrawal', [App\Http\Controllers\CashController::class, 'withdrawalForm'])->name('cash.withdrawal.store');
Route::get('/cash/movements/{cashMovement}', [App\Http\Controllers\CashController::class, 'getCashMovementDetails'])->name('cash.movement-details');

// Cash opening/closing routes
Route::get('/cash/status', [App\Http\Controllers\CashController::class, 'getCashStatus'])->name('cash.status');
Route::post('/cash/open', [App\Http\Controllers\CashController::class, 'openCash'])->name('cash.open');
Route::post('/cash/close', [App\Http\Controllers\CashController::class, 'closeCash'])->name('cash.close');

// Professional liquidation processing
Route::post('/liquidation/process', [App\Http\Controllers\LiquidationController::class, 'processLiquidation'])->name('liquidation.process');

// User management routes (Admin only)
Route::middleware(['can:viewAny,App\Models\User'])->group(function () {
    Route::resource('users', UserController::class);
    Route::patch('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
});

// User profile routes
Route::get('/profile', [UserController::class, 'profile'])->name('profile');
Route::post('/change-password', [UserController::class, 'changePassword'])->name('change-password');

}); // End of auth middleware group
