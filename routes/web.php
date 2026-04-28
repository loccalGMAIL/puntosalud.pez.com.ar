<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AgendaController;
use App\Http\Controllers\WhatsAppController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MovementTypeController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProfessionalController;
use App\Http\Controllers\ProfessionalAbsenceController;
use App\Http\Controllers\ProfessionalNoteController;
use App\Http\Controllers\ProfessionalScheduleController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SpecialtyController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Authentication routes
Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected routes
Route::middleware(['auth'])->group(function () {

    // Rutas del dashboard actualizadas para nuevas ubicaciones de vistas
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/appointments', [DashboardController::class, 'appointments'])->name('dashboard.appointments');
    Route::get('/dashboard/admin', [AdminDashboardController::class, 'index'])
        ->middleware(['module:admin_dashboard'])
        ->name('dashboard.admin');

    // Dashboard appointment status routes
    Route::post('/dashboard/appointments/{appointment}/mark-attended', [DashboardController::class, 'markAttended'])->name('dashboard.mark-attended');
    Route::post('/dashboard/appointments/{appointment}/mark-completed-paid', [DashboardController::class, 'markCompletedAndPaid'])->name('dashboard.mark-completed-paid');
    Route::post('/dashboard/appointments/{appointment}/mark-absent', [DashboardController::class, 'markAbsent'])->name('dashboard.mark-absent');
    Route::post('/dashboard/appointments/{appointment}/send-whatsapp-reminder', [DashboardController::class, 'forceWhatsAppReminder'])->name('dashboard.send-whatsapp-reminder');

    // Rutas de Profesionales (módulo: professionals)
    Route::middleware(['module:professionals'])->group(function () {
        Route::resource('professionals', ProfessionalController::class);
        Route::patch('/professionals/{professional}/toggle-status', [ProfessionalController::class, 'toggleStatus'])->name('professionals.toggle-status');

        Route::get('/professionals/{professional}/schedules', [ProfessionalScheduleController::class, 'index'])->name('professionals.schedules.index');
        Route::post('/professionals/{professional}/schedules', [ProfessionalScheduleController::class, 'store'])->name('professionals.schedules.store');
        Route::put('/professionals/{professional}/schedules/{schedule}', [ProfessionalScheduleController::class, 'update'])->name('professionals.schedules.update');
        Route::delete('/professionals/{professional}/schedules/{schedule}', [ProfessionalScheduleController::class, 'destroy'])->name('professionals.schedules.destroy');

        Route::get('/professionals/{professional}/absences/month', [ProfessionalAbsenceController::class, 'monthData'])->name('professionals.absences.month');
        Route::post('/professionals/{professional}/absences/toggle', [ProfessionalAbsenceController::class, 'toggle'])->name('professionals.absences.toggle');

        Route::resource('specialties', SpecialtyController::class)->only(['index', 'store', 'update', 'destroy']);

        // Notas internas de profesionales
        Route::get('/professionals/{professional}/notes', [ProfessionalNoteController::class, 'index'])->name('professionals.notes.index');
        Route::post('/professionals/{professional}/notes', [ProfessionalNoteController::class, 'store'])->name('professionals.notes.store');
        Route::delete('/professionals/{professional}/notes/{note}', [ProfessionalNoteController::class, 'destroy'])->name('professionals.notes.destroy');
    });

    // Rutas de Pacientes (módulo: patients)
    Route::middleware(['module:patients'])->group(function () {
        // Patient specific routes (must be before resource route)
        Route::get('/patients/{patient}/detail', [PatientController::class, 'detail'])->name('patients.detail');
        Route::get('/patients/{patient}/whatsapp-opt-outs', [PatientController::class, 'whatsappOptOuts'])->name('patients.whatsapp-opt-outs');
        Route::post('/patients/{patient}/whatsapp-opt-out/{professional}', [PatientController::class, 'toggleWhatsappOptOut'])->name('patients.whatsapp-opt-out');
        Route::resource('patients', PatientController::class);
    });

    // Rutas de Turnos (módulo: appointments)
    Route::middleware(['module:appointments'])->group(function () {
        Route::get('/appointments/available-slots', [AppointmentController::class, 'availableSlots'])->name('appointments.available-slots');
        Route::post('/appointments/urgency', [AppointmentController::class, 'storeUrgency'])->name('appointments.urgency.store');
        Route::get('/appointments/{appointment}/audit', [AppointmentController::class, 'audit'])->name('appointments.audit');
        Route::resource('appointments', AppointmentController::class);
    });

    // Rutas de Agenda (módulo: agenda)
    Route::middleware(['module:agenda'])->group(function () {
        Route::get('/agenda', [AgendaController::class, 'index'])->name('agenda.index');
    });

    // Rutas de Pagos (módulo: payments)
    Route::middleware(['module:payments'])->group(function () {
        // Payment custom routes - DEBEN IR ANTES del resource
        Route::get('/payments/{payment}/print-receipt', [PaymentController::class, 'printReceipt'])->name('payments.print-receipt');
        Route::post('/payments/{payment}/annul', [PaymentController::class, 'annul'])->name('payments.annul');
        Route::get('/payments/search-patients', [PaymentController::class, 'searchPatients'])->name('payments.search-patients');
        Route::resource('payments', PaymentController::class);
        Route::get('/payments/patients/{patient}/pending-appointments', [PaymentController::class, 'getPendingAppointments'])->name('payments.pending-appointments');

        // Payment allocation routes
        Route::post('/payments/{payment}/use-session', [PaymentController::class, 'usePackageSession'])->name('payments.use-session');
        Route::post('/payments/{payment}/allocate-single', [PaymentController::class, 'allocateSinglePayment'])->name('payments.allocate-single');
        Route::delete('/payment-appointments/{paymentAppointment}', [PaymentController::class, 'deallocatePayment'])->name('payments.deallocate');
        Route::get('/patients/{patient}/available-packages', [PaymentController::class, 'getAvailablePackages'])->name('patients.available-packages');
        Route::get('/payments/{payment}/allocation-summary', [PaymentController::class, 'getPaymentAllocationSummary'])->name('payments.allocation-summary');
        Route::post('/appointments/{appointment}/auto-allocate', [PaymentController::class, 'autoAllocatePayment'])->name('appointments.auto-allocate');
    });

    // Reportes operativos — accesibles a todos los usuarios autenticados (sin módulo)
    Route::get('/agenda/daily-schedule', [ReportController::class, 'dailySchedule'])->name('agenda.daily-schedule');
    Route::post('/agenda/daily-schedule/share-whatsapp', [ReportController::class, 'shareDailyScheduleViaWhatsApp'])->name('agenda.daily-schedule.share-whatsapp');
    Route::get('/reports/professional-liquidation', [ReportController::class, 'professionalLiquidation'])->name('reports.professional-liquidation');

    // Reports routes
    Route::middleware(['module:reports'])->group(function () {
        Route::get('/reports/daily-summary', [ReportController::class, 'dailySummary'])->name('reports.daily-summary');
        Route::get('/reports/expenses', [ReportController::class, 'expensesReport'])->name('reports.expenses');
        Route::get('/reports/expenses/export', [ReportController::class, 'exportExpensesReportCsv'])->name('reports.expenses.export');
        Route::get('/reports/expenses/print', [ReportController::class, 'printExpensesReport'])->name('reports.expenses.print');
    });

    // Movimientos de caja: accesible con módulo completo O sub-permiso específico
    Route::middleware(['permission:reports.financiero.cash'])->group(function () {
        Route::get('/reports/cash', [ReportController::class, 'cashReport'])->name('reports.cash');
        Route::get('/reports/cash/print', [ReportController::class, 'cashMovementsPrint'])->name('reports.cash.print');
    });

    // Analytics Reports (v2.10.0) — bajo módulo 'reports'
    Route::middleware(['module:reports'])->group(function () {
        Route::get('/reports/liquidaciones-historicas',        [ReportController::class, 'liquidacionesHistoricas'])->name('reports.liquidaciones-historicas');
        Route::get('/reports/liquidaciones-historicas/print',  [ReportController::class, 'printLiquidacionesHistoricas'])->name('reports.liquidaciones-historicas.print');

        Route::get('/reports/profesionales/ingresos',          [ReportController::class, 'profesionalesIngresos'])->name('reports.profesionales.ingresos');
        Route::get('/reports/profesionales/ingresos/print',    [ReportController::class, 'printProfesionalesIngresos'])->name('reports.profesionales.ingresos.print');

        Route::get('/reports/profesionales/consultas',         [ReportController::class, 'profesionalesConsultas'])->name('reports.profesionales.consultas');
        Route::get('/reports/profesionales/consultas/print',  [ReportController::class, 'printProfesionalesConsultas'])->name('reports.profesionales.consultas.print');

        Route::get('/reports/profesionales/comisiones',        [ReportController::class, 'profesionalesComisiones'])->name('reports.profesionales.comisiones');
        Route::get('/reports/profesionales/comisiones/print',  [ReportController::class, 'printProfesionalesComisiones'])->name('reports.profesionales.comisiones.print');

        Route::get('/reports/profesionales/comparativa',       [ReportController::class, 'profesionalesComparativa'])->name('reports.profesionales.comparativa');
        Route::get('/reports/profesionales/comparativa/print', [ReportController::class, 'printProfesionalesComparativa'])->name('reports.profesionales.comparativa.print');

        Route::get('/reports/pagos/tendencia',                 [ReportController::class, 'pagosTendencia'])->name('reports.pagos.tendencia');
        Route::get('/reports/pagos/tendencia/print',           [ReportController::class, 'printPagosTendencia'])->name('reports.pagos.tendencia.print');

        Route::get('/reports/pacientes/ausentismo',            [ReportController::class, 'pacientesAusentismo'])->name('reports.pacientes.ausentismo');
        Route::get('/reports/pacientes/ausentismo/print',      [ReportController::class, 'printPacientesAusentismo'])->name('reports.pacientes.ausentismo.print');
        Route::get('/reports/pacientes/retencion',             [ReportController::class, 'pacientesRetencion'])->name('reports.pacientes.retencion');
        Route::get('/reports/pacientes/retencion/print',       [ReportController::class, 'printPacientesRetencion'])->name('reports.pacientes.retencion.print');
        Route::get('/reports/pacientes/frecuencia',            [ReportController::class, 'pacientesFrecuencia'])->name('reports.pacientes.frecuencia');
        Route::get('/reports/pacientes/frecuencia/print',      [ReportController::class, 'printPacientesFrecuencia'])->name('reports.pacientes.frecuencia.print');
        Route::get('/reports/pacientes/nuevos-viejos',         [ReportController::class, 'pacientesNuevosViejos'])->name('reports.pacientes.nuevos-viejos');
        Route::get('/reports/pacientes/nuevos-viejos/print',   [ReportController::class, 'printPacientesNuevosViejos'])->name('reports.pacientes.nuevos-viejos.print');

        Route::get('/reports/ingresos-obra-social',            [ReportController::class, 'ingresosObraSocial'])->name('reports.ingresos-obra-social');
        Route::get('/reports/ingresos-obra-social/print',      [ReportController::class, 'printIngresosObraSocial'])->name('reports.ingresos-obra-social.print');
        Route::get('/reports/cobros-pendientes',               [ReportController::class, 'cobrosPendientes'])->name('reports.cobros-pendientes');
        Route::get('/reports/cobros-pendientes/print',         [ReportController::class, 'printCobrosPendientes'])->name('reports.cobros-pendientes.print');
        Route::get('/reports/flujo-caja-mensual',              [ReportController::class, 'flujoCajaMensual'])->name('reports.flujo-caja-mensual');
        Route::get('/reports/flujo-caja-mensual/print',        [ReportController::class, 'printFlujoCajaMensual'])->name('reports.flujo-caja-mensual.print');

        Route::get('/reports/cash-analysis',                   [ReportController::class, 'cashAnalysis'])->name('reports.cash-analysis');
        Route::get('/reports/cash-analysis/export',            [ReportController::class, 'exportCashAnalysisCsv'])->name('reports.cash-analysis.export');
        Route::get('/reports/cash-analysis/print',             [ReportController::class, 'printCashAnalysis'])->name('reports.cash-analysis.print');
    });

    // Rutas de Caja (módulo: cash)
    Route::middleware(['module:cash'])->group(function () {
        Route::get('/cash/daily', [App\Http\Controllers\CashController::class, 'dailyCash'])->name('cash.daily');
        Route::get('/cash/count', [App\Http\Controllers\CashController::class, 'cashCount'])->name('cash.count');
        Route::get('/cash/daily-report', [App\Http\Controllers\CashController::class, 'dailyReport'])->name('cash.daily-report');
        Route::get('/cash/expense', [App\Http\Controllers\CashController::class, 'addExpense'])->name('cash.expense-form');
        Route::post('/cash/expense', [App\Http\Controllers\CashController::class, 'addExpense'])->name('cash.expense.store');
        Route::get('/cash/withdrawal', [App\Http\Controllers\CashController::class, 'withdrawalForm'])->name('cash.withdrawal-form');
        Route::post('/cash/withdrawal', [App\Http\Controllers\CashController::class, 'withdrawalForm'])->name('cash.withdrawal.store');
        Route::get('/cash/manual-income', [App\Http\Controllers\CashController::class, 'manualIncomeForm'])->name('cash.manual-income-form');
        Route::post('/cash/manual-income', [App\Http\Controllers\CashController::class, 'manualIncomeForm'])->name('cash.manual-income.store');
        Route::get('/cash/income-receipt/{payment}', [App\Http\Controllers\CashController::class, 'printIncomeReceipt'])->name('cash.income-receipt');
        Route::get('/cash/movements/{cashMovement}', [App\Http\Controllers\CashController::class, 'getCashMovementDetails'])->name('cash.movement-details');

        // Cash opening/closing routes
        Route::get('/cash/status', [App\Http\Controllers\CashController::class, 'getCashStatus'])->name('cash.status');
        Route::post('/cash/open', [App\Http\Controllers\CashController::class, 'openCash'])->name('cash.open');
        Route::post('/cash/close', [App\Http\Controllers\CashController::class, 'closeCash'])->name('cash.close');

        // Professional liquidation processing
        Route::post('/liquidation/process', [App\Http\Controllers\LiquidationController::class, 'processLiquidation'])->name('liquidation.process');
    });

    // Rutas de WhatsApp (módulo: whatsapp)
    Route::middleware(['module:whatsapp'])->prefix('whatsapp')->name('whatsapp.')->group(function () {
        Route::get('/',           [WhatsAppController::class, 'index'])->name('index');
        Route::get('/qr-code',    [WhatsAppController::class, 'qrCode'])->name('qr-code');
        Route::get('/status',     [WhatsAppController::class, 'connectionStatus'])->name('status');
        Route::post('/disconnect',[WhatsAppController::class, 'disconnect'])->name('disconnect');
        Route::get('/settings',   [WhatsAppController::class, 'settings'])->name('settings');
        Route::post('/settings',  [WhatsAppController::class, 'saveSettings'])->name('settings.save');
        Route::post('/features',  [WhatsAppController::class, 'saveFeatures'])->name('features.save');
        Route::post('/feature',   [WhatsAppController::class, 'toggleFeature'])->name('feature.toggle');
        Route::get('/api',        [WhatsAppController::class, 'apiSettings'])->name('api');
        Route::post('/api',       [WhatsAppController::class, 'saveApiSettings'])->name('api.save');
        Route::get('/messages',      [WhatsAppController::class, 'messages'])->name('messages');
        Route::post('/test-message', [WhatsAppController::class, 'testMessage'])->name('test-message');
    });

    // Rutas de Configuración (módulo: configuration)
    Route::middleware(['module:configuration'])->group(function () {
        // Gestión de usuarios
        Route::resource('users', UserController::class)->except(['create', 'show']);
        Route::patch('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');

        // Recesos (Holidays) management
        Route::resource('recesos', App\Http\Controllers\RecessController::class)->except(['show', 'create', 'edit']);
        Route::patch('/recesos/{receso}/toggle-status', [App\Http\Controllers\RecessController::class, 'toggleStatus'])->name('recesos.toggle-status');
    });

    // Gastos externos (módulo: expenses)
    Route::middleware(['module:expenses'])->group(function () {
        Route::resource('expenses', App\Http\Controllers\ExpenseController::class)
            ->except(['create', 'edit', 'show']);
    });

    // Rutas de Sistema (módulo: system)
    Route::middleware(['module:system'])->group(function () {
        // Configuración del centro
        Route::get('/settings/center', [App\Http\Controllers\SettingsCenterController::class, 'index'])->name('settings.center');
        Route::post('/settings/center', [App\Http\Controllers\SettingsCenterController::class, 'update'])->name('settings.center.update');
        Route::post('/settings/center/toggle', [App\Http\Controllers\SettingsCenterController::class, 'toggle'])->name('settings.center.toggle');

        // Gestión de perfiles
        Route::resource('profiles', ProfileController::class)->except(['create', 'edit', 'show']);

        // Movement Types management
        Route::resource('movement-types', MovementTypeController::class)->except(['show']);
        Route::patch('/movement-types/{movementType}/toggle-active', [MovementTypeController::class, 'toggleActive'])->name('movement-types.toggle-active');

        // Activity Log
        Route::get('/activity-log', [ActivityLogController::class, 'index'])->name('activity-log.index');
    });

    // User profile routes (accesibles para todos los usuarios autenticados)
    Route::get('/profile', [UserController::class, 'profile'])->name('profile');
    Route::post('/change-password', [UserController::class, 'changePassword'])->name('change-password');

}); // End of auth middleware group
