<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\CashMovement;
use App\Models\MovementType;
use App\Models\Patient;
use App\Models\Expense;
use App\Models\Professional;
use App\Models\ProfessionalLiquidation;
use App\Services\WhatsAppService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ReportController extends Controller
{
    /**
     * Reporte de caja diaria (solo lectura)
     */
    public function cashReport(Request $request)
    {
        $date = $request->get('date', now()->format('Y-m-d'));
        $selectedDate = Carbon::parse($date);

        $previousDay = $selectedDate->copy()->subDay();
        $lastBalanceMovement = CashMovement::whereDate('created_at', '<=', $previousDay)
            ->orderBy('created_at', 'desc')
            ->first();

        $initialBalance = $lastBalanceMovement ? $lastBalanceMovement->balance_after : 0;

        $query = CashMovement::with(['user', 'movementType'])
            ->whereDate('created_at', $selectedDate);

        if ($request->filled('type')) {
            $query->whereHas('movementType', function($q) use ($request) {
                $q->where('code', $request->type);
            });
        }

        if ($request->filled('reference_type')) {
            $query->where('reference_type', $request->reference_type);
        }

        $movements = $query->orderBy('created_at', 'desc')
            ->get();

        // Calcular totales excluyendo apertura y cierre de caja
        $movementsForTotals = $movements->filter(function($movement) {
            return !in_array($movement->movementType?->code, ['cash_opening', 'cash_closing']);
        });
        $inflows = $movementsForTotals->where('amount', '>', 0)->sum('amount');
        $outflows = $movementsForTotals->where('amount', '<', 0)->sum('amount');
        $finalBalance = $initialBalance + $inflows + $outflows;

        $lastMovement = $movements->first();
        $systemFinalBalance = $lastMovement ? $lastMovement->balance_after : $initialBalance;

        // Obtener estado de caja para el día
        $cashStatus = CashMovement::getCashStatusForDate($selectedDate);

        $cashSummary = [
            'date' => $selectedDate,
            'initial_balance' => $initialBalance,
            'total_inflows' => $inflows,
            'total_outflows' => abs($outflows),
            'final_balance' => $finalBalance,
            'system_final_balance' => $systemFinalBalance,
            'is_closed' => $cashStatus['is_closed'],
            'is_open' => $cashStatus['is_open'],
            'needs_opening' => $cashStatus['needs_opening'],
            'movements_count' => $movements->count(),
        ];

        // Agrupar por tipo de movimiento excluyendo apertura y cierre
        $movementsByType = $movements
            ->filter(function($movement) {
                return !in_array($movement->movementType?->code, ['cash_opening', 'cash_closing']);
            })
            ->groupBy(function($movement) {
                return $movement->movementType?->code ?? 'unknown';
            })
            ->map(function ($group, $typeCode) {
                $firstMovement = $group->first();
                return [
                    'type' => $typeCode,
                    'type_name' => $firstMovement->movementType?->name ?? ucfirst($typeCode),
                    'icon' => $firstMovement->movementType?->icon ?? '📋',
                    'inflows' => $group->where('amount', '>', 0)->sum('amount'),
                    'outflows' => abs($group->where('amount', '<', 0)->sum('amount')),
                    'count' => $group->count(),
                ];
            });

        return view('reports.cash', compact('cashSummary', 'movements', 'movementsByType'));
    }

    /**
     * Imprimir movimientos de caja del día
     */
    public function cashMovementsPrint(Request $request)
    {
        $date = $request->get('date', now()->format('Y-m-d'));
        $selectedDate = Carbon::parse($date);

        $previousDay = $selectedDate->copy()->subDay();
        $lastBalanceMovement = CashMovement::whereDate('created_at', '<=', $previousDay)
            ->orderBy('created_at', 'desc')
            ->first();

        $initialBalance = $lastBalanceMovement ? $lastBalanceMovement->balance_after : 0;

        // Obtener movimientos con referencias para método de pago
        $movements = CashMovement::with(['user', 'movementType'])
            ->with(['reference' => function($morphTo) {
                $morphTo->morphWith([
                    \App\Models\Payment::class => ['paymentDetails']
                ]);
            }])
            ->whereDate('created_at', $selectedDate)
            ->orderBy('created_at')
            ->get();

        // Calcular totales excluyendo apertura y cierre de caja
        $movementsForTotals = $movements->filter(function($movement) {
            return !in_array($movement->movementType?->code, ['cash_opening', 'cash_closing']);
        });
        $inflows = $movementsForTotals->where('amount', '>', 0)->sum('amount');
        $outflows = $movementsForTotals->where('amount', '<', 0)->sum('amount');
        $finalBalance = $initialBalance + $inflows + $outflows;

        $cashSummary = [
            'date' => $selectedDate,
            'initial_balance' => $initialBalance,
            'total_inflows' => $inflows,
            'total_outflows' => abs($outflows),
            'final_balance' => $finalBalance,
        ];

        return view('reports.cash-movements-print', compact('cashSummary', 'movements'));
    }

    /**
     * Listado diario de pacientes a atender por profesional
     */
    public function dailySchedule(Request $request)
    {
        $date = $request->get('date', now()->format('Y-m-d'));
        $professionalId = $request->get('professional_id');
        $selectedDate = Carbon::parse($date);

        // Obtener profesionales activos que tienen turnos en la fecha seleccionada
        $professionalsWithAppointments = Professional::active()
            ->with(['specialty', 'appointments' => function ($query) use ($selectedDate) {
                $query->forDate($selectedDate)->where('status', '!=', 'cancelled');
            }])
            ->whereHas('appointments', function ($query) use ($selectedDate) {
                $query->forDate($selectedDate)->where('status', '!=', 'cancelled');
            })
            ->orderBy('last_name')
            ->get()
            ->map(function ($professional) {
                return [
                    'id' => $professional->id,
                    'first_name' => $professional->first_name,
                    'last_name' => $professional->last_name,
                    'full_name' => $professional->full_name,
                    'specialty' => $professional->specialty,
                    'appointments_count' => $professional->appointments->count(),
                    'first_appointment_time' => $professional->appointments->min('appointment_date'),
                    'last_appointment_time' => $professional->appointments->max('appointment_date'),
                ];
            });

        // Obtener todos los profesionales activos para el dropdown
        $allProfessionals = Professional::active()
            ->with('specialty')
            ->orderBy('last_name')
            ->get();

        // Si no se especifica profesional, mostrar vista de selección
        if (! $professionalId) {
            return view('agenda.daily-schedule-select', compact('allProfessionals', 'professionalsWithAppointments', 'selectedDate'));
        }

        $reportData = $this->buildDailyScheduleData((int) $professionalId, $selectedDate);

        // Si es para imprimir, devolver vista de impresión
        if ($request->get('print') === '1') {
            return view('agenda.daily-schedule-print', compact('reportData'));
        }

        // Vista normal
        return view('agenda.daily-schedule', compact('reportData', 'allProfessionals'));
    }

    public function shareDailyScheduleViaWhatsApp(Request $request, WhatsAppService $whatsApp): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'professional_id' => 'required|exists:professionals,id',
            'date'            => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first() ?: 'Datos inválidos.',
            ], 422);
        }

        $validated = $validator->validated();
        $professional = Professional::findOrFail($validated['professional_id']);

        $conn = $whatsApp->validateConnection();
        if (! ($conn['ok'] ?? false)) {
            return response()->json([
                'success' => false,
                'message' => $conn['message'] ?? 'WhatsApp no está disponible.',
            ], 422);
        }

        $recipient = $whatsApp->validateRecipient($professional->phone);
        if (! ($recipient['ok'] ?? false)) {
            $msg = ($recipient['error_code'] ?? '') === 'no_phone'
                ? 'El profesional no tiene un número de teléfono registrado.'
                : ($recipient['message'] ?? 'Teléfono del profesional inválido.');

            return response()->json([
                'success' => false,
                'message' => $msg,
            ], 422);
        }

        $formatted = $recipient['phone'];

        $reportData = $this->buildDailyScheduleData(
            (int) $validated['professional_id'],
            Carbon::parse($validated['date'])
        );

        $pdf = Pdf::loadView('agenda.daily-schedule-print', [
            'reportData' => $reportData,
            'isPdf'      => true,
        ])
            ->setPaper('a4')
            ->setOption('isRemoteEnabled', false)
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('chroot', public_path());
        $base64 = base64_encode($pdf->output());

        $safeName = Str::slug($professional->full_name, '_');
        if (empty($safeName)) {
            $safeName = 'profesional_' . $professional->id;
        }

        $filename = 'listado-' . $reportData['date']->format('Y-m-d') . '-' . $safeName . '.pdf';
        $caption  = 'Listado de pacientes ' . $reportData['date']->format('d/m/Y');

        $result = $whatsApp->sendMediaFile($formatted, $base64, $filename, $caption);

        return response()->json([
            'success' => (bool) ($result['success'] ?? false),
            'message' => ($result['success'] ?? false)
                ? 'Listado enviado por WhatsApp al profesional.'
                : 'No se pudo enviar el mensaje. Intentá nuevamente.',
        ]);
    }

    private function buildDailyScheduleData(int $professionalId, Carbon $selectedDate): array
    {
        $professional = Professional::with('specialty')->findOrFail($professionalId);

        $appointments = Appointment::with(['office', 'patient', 'paymentAppointments.payment'])
            ->where('professional_id', $professionalId)
            ->forDate($selectedDate)
            ->where('status', '!=', 'cancelled')
            ->orderBy('appointment_date')
            ->get()
            ->map(function ($appointment) {
                return [
                    'id' => $appointment->id,
                    'time' => $appointment->appointment_date->format('H:i'),
                    'patient_name' => $appointment->patient->full_name,
                    'patient_phone' => $appointment->patient->phone,
                    'patient_email' => $appointment->patient->email,
                    'patient_dni' => $appointment->patient->dni,
                    'patient_insurance' => $appointment->patient->insurance_company,
                    'estimated_amount' => $appointment->estimated_amount ?? 0,
                    'final_amount' => $appointment->final_amount,
                    'status' => $appointment->status,
                    'status_label' => $this->getStatusLabel($appointment->status),
                    'is_paid' => $appointment->paymentAppointments()->exists(),
                    'payment_method' => $appointment->paymentAppointments->first()?->payment?->payment_method,
                    'notes' => $appointment->notes,
                    'office' => $appointment->office?->name ?? 'No asignado',
                    'is_urgency' => $appointment->is_urgency,
                    'is_between_turn' => $appointment->is_between_turn,
                ];
            })
            ->sortByDesc('is_urgency')
            ->values();

        $stats = [
            'total_appointments' => $appointments->count(),
            'scheduled' => $appointments->where('status', 'scheduled')->count(),
            'attended' => $appointments->where('status', 'attended')->count(),
            'cancelled' => $appointments->where('status', 'cancelled')->count(),
            'absent' => $appointments->where('status', 'absent')->count(),
            'paid_appointments' => $appointments->where('is_paid', true)->count(),
            'total_estimated' => $appointments->sum('estimated_amount'),
            'total_final' => $appointments->whereNotNull('final_amount')->sum('final_amount'),
        ];

        return [
            'professional' => $professional,
            'date' => $selectedDate,
            'appointments' => $appointments,
            'stats' => $stats,
            'generated_at' => now(),
            'generated_by' => Auth::user()?->name ?? 'Sistema',
        ];
    }

    /**
     * Resumen diario de pacientes por profesional
     */
    public function dailySummary(Request $request)
    {
        $date = $request->get('date', now()->format('Y-m-d'));
        $selectedDate = Carbon::parse($date);

        $professionals = Professional::active()
            ->with(['specialty', 'appointments' => function ($query) use ($selectedDate) {
                $query->forDate($selectedDate)
                    ->with(['patient', 'paymentAppointments.payment']);
            }])
            ->orderBy('last_name')
            ->get()
            ->map(function ($professional) {
                $appointments = $professional->appointments;

                return [
                    'id' => $professional->id,
                    'name' => $professional->full_name,
                    'specialty' => $professional->specialty->name,
                    'total_appointments' => $appointments->count(),
                    'scheduled' => $appointments->where('status', 'scheduled')->count(),
                    'attended' => $appointments->where('status', 'attended')->count(),
                    'cancelled' => $appointments->where('status', 'cancelled')->count(),
                    'absent' => $appointments->where('status', 'absent')->count(),
                    'paid' => $appointments->filter(fn ($apt) => $apt->paymentAppointments->isNotEmpty())->count(),
                    'total_estimated' => $appointments->sum('estimated_amount'),
                    'total_final' => $appointments->whereNotNull('final_amount')->sum('final_amount'),
                    'first_appointment' => $appointments->min('appointment_date'),
                    'last_appointment' => $appointments->max('appointment_date'),
                ];
            })
            ->filter(function ($professional) {
                return $professional['total_appointments'] > 0;
            });

        $summaryData = [
            'date' => $selectedDate,
            'professionals' => $professionals,
            'totals' => [
                'total_appointments' => $professionals->sum('total_appointments'),
                'total_scheduled' => $professionals->sum('scheduled'),
                'total_attended' => $professionals->sum('attended'),
                'total_cancelled' => $professionals->sum('cancelled'),
                'total_absent' => $professionals->sum('absent'),
                'total_paid' => $professionals->sum('paid'),
                'total_estimated' => $professionals->sum('total_estimated'),
                'total_final' => $professionals->sum('total_final'),
            ],
            'generated_at' => now(),
            'generated_by' => Auth::user()?->name ?? 'Sistema',
        ];

        if ($request->get('print') === '1') {
            return view('reports.daily-summary-print', compact('summaryData'));
        }

        return view('reports.daily-summary', compact('summaryData'));
    }

    /**
     * Reporte de liquidación diaria para profesional
     */
    public function professionalLiquidation(Request $request)
    {
        $date = $request->get('date', now()->format('Y-m-d'));
        $professionalId = $request->get('professional_id');
        $selectedDate = Carbon::parse($date);

        // Obtener profesionales con turnos atendidos en la fecha
        $professionalsWithLiquidation = Professional::active()
            ->with(['specialty', 'appointments' => function ($query) use ($selectedDate) {
                $query->forDate($selectedDate)->attended();
            }])
            ->whereHas('appointments', function ($query) use ($selectedDate) {
                $query->forDate($selectedDate)->attended();
            })
            ->orderBy('last_name')
            ->get()
            ->map(function ($professional) use ($selectedDate) {
                // NUEVO v2.6.0: Calcular considerando el routing de pagos
                $attendedAppointmentIds = $professional->appointments->pluck('id');

                // Obtener payment_details recibidos por el centro (solo pendientes de liquidar)
                $centroPaymentDetails = \App\Models\PaymentDetail::whereHas('payment.paymentAppointments', function($q) use ($attendedAppointmentIds) {
                        $q->whereIn('appointment_id', $attendedAppointmentIds);
                    })
                    ->where('received_by', 'centro')
                    ->whereNull('liquidation_id')
                    ->get();

                // Obtener payment_details recibidos directamente por el profesional (solo pendientes de liquidar)
                $professionalPaymentDetails = \App\Models\PaymentDetail::whereHas('payment.paymentAppointments', function($q) use ($attendedAppointmentIds) {
                        $q->whereIn('appointment_id', $attendedAppointmentIds);
                    })
                    ->where('received_by', 'profesional')
                    ->whereNull('liquidation_id')
                    ->get();

                $totalCollectedByCenter = $centroPaymentDetails->sum('amount');
                $totalCollectedByProfessional = $professionalPaymentDetails->sum('amount');
                $totalAmount = $totalCollectedByCenter + $totalCollectedByProfessional;

                // Calcular comisión solo sobre pagos al centro
                $professionalCommission = $professional->calculateCommission($totalCollectedByCenter);

                // Calcular parte del centro sobre pagos directos
                $clinicPercentage = 100 - $professional->commission_percentage;
                $clinicAmountFromDirect = $totalCollectedByProfessional * ($clinicPercentage / 100);

                // Obtener reintegros del día para este profesional (usando referencias polimórficas)
                $refunds = \App\Models\CashMovement::byType('expense')
                    ->where('reference_type', 'App\Models\Professional')
                    ->where('reference_id', $professional->id)
                    ->whereDate('created_at', $selectedDate)
                    ->get();

                $totalRefunds = $refunds->sum(function($refund) {
                    return abs($refund->amount); // Los gastos son negativos, convertir a positivo
                });

                // Calcular monto neto
                $netProfessionalAmount = $professionalCommission - $clinicAmountFromDirect - $totalRefunds;

                return [
                    'id' => $professional->id,
                    'full_name' => $professional->full_name,
                    'specialty' => $professional->specialty,
                    'attended_count' => $professional->appointments->count(),
                    'total_amount' => $totalAmount,
                    'total_collected_by_center' => $totalCollectedByCenter,
                    'total_collected_by_professional' => $totalCollectedByProfessional,
                    'refunds' => $totalRefunds,
                    'professional_amount' => $netProfessionalAmount,
                    'clinic_amount' => $totalCollectedByCenter - $professionalCommission,
                    'has_pending_payments' => $centroPaymentDetails->count() > 0 || $professionalPaymentDetails->count() > 0,
                ];
            })
            ->filter(function ($professional) {
                // CORREGIDO v2.6.2: Mostrar si tiene turnos atendidos Y pagos pendientes de liquidar
                // No filtrar por monto $0 porque puede ser comisión 0%, pagos directos, o reintegros
                // Lo importante es que tenga payment_details sin liquidar (whereNull('liquidation_id'))
                return $professional['attended_count'] > 0 && $professional['has_pending_payments'];
            });

        // Obtener todos los profesionales para el dropdown
        $allProfessionals = Professional::active()
            ->with('specialty')
            ->orderBy('last_name')
            ->get();

        // Obtener liquidaciones ya realizadas en el día
        $completedLiquidations = \App\Models\ProfessionalLiquidation::with(['professional.specialty', 'details'])
            ->whereDate('liquidation_date', $selectedDate)
            ->orderBy('professional_id')
            ->orderBy('created_at')
            ->get()
            ->groupBy('professional_id')
            ->map(function ($liquidations, $professionalId) {
                $professional = $liquidations->first()->professional;
                $liquidationsList = $liquidations->map(function ($liq, $index) {
                    // Contar turnos únicos de esta liquidación específica
                    $uniqueAppointments = $liq->details->pluck('appointment_id')->unique()->count();

                    return [
                        'id' => $liq->id,
                        'number' => $index + 1,
                        'amount' => $liq->net_professional_amount,
                        'created_at' => $liq->created_at,
                        'appointments_count' => $uniqueAppointments,
                    ];
                });

                return [
                    'professional' => $professional,
                    'liquidations' => $liquidationsList,
                    'total_liquidations' => $liquidations->count(),
                    'total_amount' => $liquidations->sum('net_professional_amount'),
                ];
            });

        // Si no se especifica profesional, mostrar vista de selección
        if (! $professionalId) {
            return view('reports.professional-liquidation-select', compact('allProfessionals', 'professionalsWithLiquidation', 'selectedDate', 'completedLiquidations'));
        }

        // Obtener el profesional seleccionado
        $professional = Professional::with('specialty')->findOrFail($professionalId);

        // Obtener turnos atendidos del día con información de pagos
        $attendedAppointments = Appointment::with(['patient', 'paymentAppointments.payment.paymentDetails'])
            ->where('professional_id', $professionalId)
            ->forDate($selectedDate)
            ->attended()
            ->orderBy('appointment_date')
            ->get()
            ->map(function ($appointment) use ($selectedDate) {
                $paymentAppointment = $appointment->paymentAppointments->first();
                $payment = $paymentAppointment ? $paymentAppointment->payment : null;

                // Determinar si fue pago anticipado o del día
                $isPrepaid = false;
                $paymentDate = null;
                $receivedBy = null;
                $paymentMethod = null;
                $paymentMethodsArray = [];
                $isMultiplePayment = false;

                if ($payment) {
                    $paymentDate = Carbon::parse($payment->payment_date);
                    $isPrepaid = ! $paymentDate->isSameDay($selectedDate);

                    // NUEVO v2.6.0: Manejar múltiples payment_details
                    $paymentDetails = $payment->paymentDetails;

                    if ($paymentDetails->isNotEmpty()) {
                        $isMultiplePayment = $paymentDetails->count() > 1;

                        // Obtener todos los métodos de pago
                        $paymentMethodsArray = $paymentDetails->map(function($detail) {
                            return [
                                'method' => $detail->payment_method,
                                'amount' => $detail->amount,
                                'received_by' => $detail->received_by,
                            ];
                        })->toArray();

                        // Determinar método de pago a mostrar
                        if ($isMultiplePayment) {
                            // Múltiples métodos: concatenar
                            $methods = $paymentDetails->pluck('payment_method')->unique()->toArray();
                            $paymentMethod = 'multiple'; // Marcador especial
                        } else {
                            // Un solo método
                            $paymentMethod = $paymentDetails->first()->payment_method;
                        }

                        // Determinar receptor
                        $receivers = $paymentDetails->pluck('received_by')->unique();
                        if ($receivers->count() === 1) {
                            // Todos van al mismo receptor
                            $receivedBy = $receivers->first();
                        } else {
                            // Pago mixto (parte al centro, parte al profesional)
                            $receivedBy = 'mixed';
                        }
                    }
                }

                return [
                    'id' => $appointment->id,
                    'time' => $appointment->appointment_date->format('H:i'),
                    'patient_name' => $appointment->patient->full_name,
                    'patient_dni' => $appointment->patient->dni,
                    'final_amount' => $appointment->final_amount ?? 0,
                    'is_paid' => $paymentAppointment ? true : false,
                    'payment_method' => $paymentMethod,
                    'payment_methods_array' => $paymentMethodsArray, // Detalle completo
                    'is_multiple_payment' => $isMultiplePayment,
                    'received_by' => $receivedBy,
                    'payment_date' => $paymentDate ? $paymentDate->format('d/m/Y') : null,
                    'is_prepaid' => $isPrepaid,
                    'payment_type' => $payment ? $payment->payment_type : null,
                    'receipt_number' => $payment ? $payment->receipt_number : null,
                    'is_urgency' => $appointment->is_urgency,
                    'duration' => $appointment->duration,
                ];
            });

        // NUEVO v2.6.0: Calcular estadísticas de liquidación considerando el routing de pagos

        // Obtener IDs de appointments atendidos para filtrar payment_details
        $attendedAppointmentIds = $attendedAppointments->pluck('id');

        // Obtener payment_details recibidos por el CENTRO
        $centroPaymentDetails = \App\Models\PaymentDetail::whereHas('payment.paymentAppointments', function($q) use ($attendedAppointmentIds) {
                $q->whereIn('appointment_id', $attendedAppointmentIds);
            })
            ->where('received_by', 'centro')
            ->get();

        // Obtener payment_details recibidos DIRECTAMENTE por el profesional
        $professionalPaymentDetails = \App\Models\PaymentDetail::whereHas('payment.paymentAppointments', function($q) use ($attendedAppointmentIds) {
                $q->whereIn('appointment_id', $attendedAppointmentIds);
            })
            ->where('received_by', 'profesional')
            ->get();

        // Calcular totales por destino de pago
        $totalCollectedByCenter = $centroPaymentDetails->sum('amount');
        $totalCollectedByProfessional = $professionalPaymentDetails->sum('amount');
        $totalAmount = $totalCollectedByCenter + $totalCollectedByProfessional;

        // Calcular comisión del profesional solo sobre pagos recibidos por el centro
        $professionalCommission = $professional->calculateCommission($totalCollectedByCenter);

        // Calcular la parte del centro sobre los pagos directos al profesional
        $clinicPercentage = 100 - $professional->commission_percentage;
        $clinicAmountFromDirect = $totalCollectedByProfessional * ($clinicPercentage / 100);

        // Obtener reintegros del día para este profesional (usando referencias polimórficas)
        $refunds = \App\Models\CashMovement::byType('expense')
            ->where('reference_type', 'App\Models\Professional')
            ->where('reference_id', $professionalId)
            ->whereDate('created_at', $selectedDate)
            ->get();

        $totalRefunds = $refunds->sum(function($refund) {
            return abs($refund->amount); // Los gastos son negativos, convertir a positivo
        });

        // Calcular montos finales
        $clinicAmount = $totalCollectedByCenter - $professionalCommission;
        $netProfessionalAmount = $professionalCommission - $clinicAmountFromDirect - $totalRefunds;
        $finalProfessionalAmount = $netProfessionalAmount; // Alias para compatibilidad

        // Separar por tipo de pago
        $prepaidAppointments = $attendedAppointments->where('is_prepaid', true);
        $todayPaidAppointments = $attendedAppointments->where('is_paid', true)->where('is_prepaid', false);
        $unpaidAppointments = $attendedAppointments->where('is_paid', false);

        // Calcular número de liquidación del día (para preview)
        $liquidationNumber = \App\Models\ProfessionalLiquidation::where('professional_id', $professionalId)
            ->whereDate('liquidation_date', $selectedDate)
            ->count() + 1;

        // Obtener liquidaciones realizadas en el día y agrupar turnos
        $liquidationsGrouped = \App\Models\ProfessionalLiquidation::with(['details.appointment.patient', 'details.appointment.paymentAppointments.payment.paymentDetails'])
            ->where('professional_id', $professionalId)
            ->whereDate('liquidation_date', $selectedDate)
            ->orderBy('created_at')
            ->get()
            ->map(function ($liquidation, $index) use ($attendedAppointments, $selectedDate) {
                // Obtener IDs de turnos de esta liquidación
                $appointmentIds = $liquidation->details->pluck('appointment_id')->unique();

                // Filtrar turnos que pertenecen a esta liquidación
                $liquidationAppointments = $attendedAppointments->filter(function ($apt) use ($appointmentIds) {
                    return $appointmentIds->contains($apt['id']);
                });

                return [
                    'number' => $index + 1,
                    'id' => $liquidation->id,
                    'amount' => $liquidation->net_professional_amount,
                    'created_at' => $liquidation->created_at,
                    'appointments' => $liquidationAppointments,
                    'appointments_count' => $liquidationAppointments->count(),
                ];
            });

        // Turnos sin liquidar (pendientes)
        $liquidatedAppointmentIds = $liquidationsGrouped->flatMap(function ($liq) {
            return $liq['appointments']->pluck('id');
        });
        $pendingAppointments = $attendedAppointments->filter(function ($apt) use ($liquidatedAppointmentIds) {
            return !$liquidatedAppointmentIds->contains($apt['id']);
        });

        $liquidationData = [
            'professional' => $professional,
            'date' => $selectedDate,
            'liquidation_number' => $liquidationNumber,
            'appointments' => $attendedAppointments,
            'liquidations_grouped' => $liquidationsGrouped,
            'pending_appointments' => $pendingAppointments,
            'prepaid_appointments' => $prepaidAppointments,
            'today_paid_appointments' => $todayPaidAppointments,
            'unpaid_appointments' => $unpaidAppointments,
            'refunds' => $refunds,
            'totals' => [
                'total_amount' => $totalAmount,
                'total_collected_by_center' => $totalCollectedByCenter,
                'total_collected_by_professional' => $totalCollectedByProfessional,
                'professional_commission' => $professionalCommission,
                'clinic_amount_from_direct' => $clinicAmountFromDirect,
                'total_refunds' => $totalRefunds,
                'net_professional_amount' => $netProfessionalAmount,
                'professional_amount' => $finalProfessionalAmount,
                'clinic_amount' => $clinicAmount,
                'clinic_percentage' => $clinicPercentage,
                'commission_percentage' => $professional->commission_percentage,
                'prepaid_amount' => $prepaidAppointments->sum('final_amount'),
                'today_paid_amount' => $todayPaidAppointments->sum('final_amount'),
                'unpaid_amount' => $unpaidAppointments->sum('final_amount'),
                'prepaid_professional' => $professional->calculateCommission($prepaidAppointments->sum('final_amount')),
                'today_paid_professional' => $professional->calculateCommission($todayPaidAppointments->sum('final_amount')),
                'unpaid_professional' => $professional->calculateCommission($unpaidAppointments->sum('final_amount')),
            ],
            'payment_methods_summary' => $attendedAppointments->where('is_paid', true)->groupBy('payment_method')->map(function ($group, $method) {
                return [
                    'method' => $method,
                    'count' => $group->count(),
                    'amount' => $group->sum('final_amount'),
                ];
            }),
            'generated_at' => now(),
            'generated_by' => Auth::user()?->name ?? 'Sistema',
        ];

        // Si es para imprimir, devolver vista de impresión
        if ($request->get('print') === '1') {
            // Si se especifica liquidation_id, filtrar para mostrar solo esa liquidación
            $specificLiquidationId = $request->get('liquidation_id');
            if ($specificLiquidationId) {
                // Filtrar solo la liquidación específica
                $filteredLiquidation = $liquidationsGrouped->firstWhere('id', (int) $specificLiquidationId);

                if ($filteredLiquidation) {
                    // Crear un nuevo liquidationData con solo esa liquidación
                    $liquidationData['liquidations_grouped'] = collect([$filteredLiquidation]);
                    $liquidationData['pending_appointments'] = collect([]); // No mostrar pendientes en impresión individual
                    $liquidationData['is_single_liquidation'] = true;
                    $liquidationData['single_liquidation_number'] = $filteredLiquidation['number'];
                }
            }

            return view('reports.professional-liquidation-print', compact('liquidationData'));
        }

        // Vista normal
        return view('reports.professional-liquidation', compact('liquidationData', 'allProfessionals'));
    }

    /**
     * Informe de gastos por período
     */
    public function expensesReport(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo   = $request->get('date_to', now()->format('Y-m-d'));
        $movementTypeId = $request->get('movement_type_id');

        $startDate = Carbon::parse($dateFrom)->startOfDay();
        $endDate   = Carbon::parse($dateTo)->endOfDay();

        $movements = $this->buildUnifiedExpenses($startDate, $endDate, $movementTypeId);

        $totalAmount = $movements->sum('amount');
        $totalCount  = $movements->count();

        $byType = $movements->groupBy(fn ($m) => $m['type']?->id)->map(fn ($items) => [
            'name'  => $items->first()['type']?->name ?? '-',
            'icon'  => $items->first()['type']?->icon ?? '📋',
            'count' => $items->count(),
            'total' => $items->sum('amount'),
        ])->sortByDesc('total');

        $topType = $byType->first();

        $expenseTypes = MovementType::active()
            ->whereIn('category', ['expense_detail', 'withdrawal_detail'])
            ->orderBy('category')->orderBy('order')->get();

        return view('reports.expenses', compact(
            'movements', 'totalAmount', 'totalCount', 'byType', 'topType',
            'expenseTypes', 'dateFrom', 'dateTo', 'movementTypeId'
        ));
    }

    /**
     * Exportar informe de gastos como CSV (formato amigable para Excel)
     */
    public function exportExpensesReportCsv(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo   = $request->get('date_to', now()->format('Y-m-d'));
        $movementTypeId = $request->get('movement_type_id');

        $startDate = Carbon::parse($dateFrom)->startOfDay();
        $endDate   = Carbon::parse($dateTo)->endOfDay();

        $movements = $this->buildUnifiedExpenses($startDate, $endDate, $movementTypeId);

        $totalAmount = $movements->sum('amount');
        $totalCount  = $movements->count();

        $byType = $movements->groupBy(fn ($m) => $m['type']?->id)->map(fn ($items) => [
            'name'  => $items->first()['type']?->name ?? '-',
            'count' => $items->count(),
            'total' => $items->sum('amount'),
        ])->sortByDesc('total');

        $filename = 'gastos_' . $dateFrom . '_' . $dateTo . '.csv';

        $callback = function () use ($movements, $byType, $totalAmount, $totalCount, $dateFrom, $dateTo) {
            $f = fopen('php://output', 'w');
            // BOM UTF-8 para que Excel detecte el encoding
            fprintf($f, chr(0xEF).chr(0xBB).chr(0xBF));

            // Encabezado
            fputcsv($f, ['INFORME DE GASTOS'], ';');
            fputcsv($f, ["Período: $dateFrom al $dateTo"], ';');
            fputcsv($f, [], ';');

            // Resumen
            fputcsv($f, ['RESUMEN'], ';');
            fputcsv($f, ['Total Gastos',    number_format($totalAmount, 2, ',', '.')], ';');
            fputcsv($f, ['Cantidad de Registros', $totalCount], ';');
            fputcsv($f, [], ';');

            // Por tipo
            fputcsv($f, ['ANÁLISIS POR TIPO'], ';');
            fputcsv($f, ['Tipo de Gasto', 'Cantidad', 'Total'], ';');
            foreach ($byType as $item) {
                fputcsv($f, [
                    $item['name'],
                    $item['count'],
                    number_format($item['total'], 2, ',', '.'),
                ], ';');
            }
            fputcsv($f, [], ';');

            // Detalle
             fputcsv($f, ['DETALLE DE GASTOS'], ';');
             fputcsv($f, ['Fecha', 'Hora', 'Origen', 'Tipo de Gasto', 'Descripción', 'Monto', 'Registrado por'], ';');
             foreach ($movements as $m) {
                 fputcsv($f, [
                     $m['date']->format('d/m/Y'),
                     $m['time'] ?? '',
                     $m['origin_label'],
                     $m['type']?->name ?? '-',
                     $m['description'] ?? '',
                     number_format($m['amount'], 2, ',', '.'),
                     $m['user']?->name ?? 'Sistema',
                 ], ';');
             }

            fclose($f);
        };

        return response()->stream($callback, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Imprimir informe de gastos (vista HTML para impresión en browser)
     */
    public function printExpensesReport(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo   = $request->get('date_to', now()->format('Y-m-d'));
        $movementTypeId = $request->get('movement_type_id');

        $startDate = Carbon::parse($dateFrom)->startOfDay();
        $endDate   = Carbon::parse($dateTo)->endOfDay();

        $movements = $this->buildUnifiedExpenses($startDate, $endDate, $movementTypeId);

        $totalAmount = $movements->sum('amount');
        $totalCount  = $movements->count();
        $avgAmount   = $totalCount > 0 ? $totalAmount / $totalCount : 0;

        $byType = $movements->groupBy(fn ($m) => $m['type']?->id)->map(fn ($items) => [
            'name'  => $items->first()['type']?->name ?? '-',
            'icon'  => $items->first()['type']?->icon ?? '📋',
            'count' => $items->count(),
            'total' => $items->sum('amount'),
        ])->sortByDesc('total');

        return view('reports.expenses-print', compact(
            'movements', 'totalAmount', 'totalCount', 'avgAmount', 'byType', 'dateFrom', 'dateTo'
        ));
    }

    private function buildUnifiedExpenses(Carbon $startDate, Carbon $endDate, ?string $movementTypeId)
    {
        $cashQuery = CashMovement::with(['movementType', 'user'])
            ->whereHas('movementType', fn ($q) => $q->whereIn('category', ['expense_detail', 'withdrawal_detail']))
            ->whereBetween('created_at', [$startDate, $endDate]);

        if ($movementTypeId) {
            $cashQuery->where('movement_type_id', $movementTypeId);
        }

        $cash = $cashQuery->orderBy('created_at', 'desc')->get()->map(function ($m) {
            return [
                'sort_at' => $m->created_at,
                'date' => $m->created_at,
                'time' => $m->created_at->format('H:i'),
                'type' => $m->movementType,
                'amount' => abs($m->amount),
                'description' => $m->description,
                'user' => $m->user,
                'origin' => 'cash',
                'origin_label' => 'Caja',
            ];
        });

        $externalQuery = Expense::with(['movementType', 'creator'])
            ->whereBetween('expense_date', [$startDate->toDateString(), $endDate->toDateString()]);

        if ($movementTypeId) {
            $externalQuery->where('movement_type_id', $movementTypeId);
        }

        $external = $externalQuery->orderBy('expense_date', 'desc')->orderBy('id', 'desc')->get()->map(function ($e) {
            $date = $e->expense_date instanceof Carbon ? $e->expense_date : Carbon::parse($e->expense_date);

            return [
                'sort_at' => $date->copy()->endOfDay(),
                'date' => $date,
                'time' => null,
                'type' => $e->movementType,
                'amount' => (float) $e->amount,
                'description' => $e->description,
                'user' => $e->creator,
                'origin' => 'external',
                'origin_label' => 'Externo',
            ];
        });

        return $cash->concat($external)->sortByDesc('sort_at')->values();
    }

    // =========================================================================
    // ANALYTICS REPORTS (v2.10.0)
    // =========================================================================

    /**
     * Liquidaciones históricas por período
     */
    public function liquidacionesHistoricas(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo   = $request->get('date_to', now()->format('Y-m-d'));
        $professionalId = $request->get('professional_id');
        $paymentStatus  = $request->get('payment_status', 'all');

        $query = ProfessionalLiquidation::with(['professional.specialty'])
            ->whereBetween('liquidation_date', [$dateFrom, $dateTo])
            ->orderBy('liquidation_date', 'desc');

        if ($professionalId) {
            $query->where('professional_id', $professionalId);
        }
        if ($paymentStatus !== 'all') {
            $query->where('payment_status', $paymentStatus);
        }

        $liquidations = $query->get();
        $byMonth      = $liquidations->groupBy(fn($l) => $l->liquidation_date->format('Y-m'));

        $totals = [
            'total_collected'      => $liquidations->sum('total_collected'),
            'professional_amount'  => $liquidations->sum('net_professional_amount'),
            'clinic_amount'        => $liquidations->sum('clinic_amount'),
            'count'                => $liquidations->count(),
            'pending_count'        => $liquidations->where('payment_status', 'pending')->count(),
            'paid_count'           => $liquidations->where('payment_status', 'paid')->count(),
        ];

        $allProfessionals = Professional::active()->ordered()->get();

        return view('reports.liquidaciones-historicas', compact(
            'liquidations', 'byMonth', 'totals',
            'allProfessionals', 'dateFrom', 'dateTo', 'professionalId', 'paymentStatus'
        ));
    }

    /**
     * Imprimir liquidaciones históricas
     */
    public function printLiquidacionesHistoricas(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo   = $request->get('date_to', now()->format('Y-m-d'));
        $professionalId = $request->get('professional_id');
        $paymentStatus  = $request->get('payment_status', 'all');

        $query = ProfessionalLiquidation::with(['professional.specialty'])
            ->whereBetween('liquidation_date', [$dateFrom, $dateTo])
            ->orderBy('liquidation_date', 'desc');

        if ($professionalId) {
            $query->where('professional_id', $professionalId);
        }
        if ($paymentStatus !== 'all') {
            $query->where('payment_status', $paymentStatus);
        }

        $liquidations = $query->get();
        $byMonth      = $liquidations->groupBy(fn($l) => $l->liquidation_date->format('Y-m'));

        $totals = [
            'total_collected'      => $liquidations->sum('total_collected'),
            'professional_amount'  => $liquidations->sum('net_professional_amount'),
            'clinic_amount'        => $liquidations->sum('clinic_amount'),
            'count'                => $liquidations->count(),
            'pending_count'        => $liquidations->where('payment_status', 'pending')->count(),
            'paid_count'           => $liquidations->where('payment_status', 'paid')->count(),
        ];

        $professionalName = $professionalId
            ? Professional::find($professionalId)?->full_name
            : null;

        return view('reports.liquidaciones-historicas-print', compact(
            'liquidations', 'byMonth', 'totals',
            'dateFrom', 'dateTo', 'professionalName', 'paymentStatus'
        ));
    }

    /**
     * Informe de ingresos por profesional
     */
    public function profesionalesIngresos(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo   = $request->get('date_to', now()->format('Y-m-d'));
        $professionalId = $request->get('professional_id');

        $startDate = Carbon::parse($dateFrom)->startOfDay();
        $endDate   = Carbon::parse($dateTo)->endOfDay();

        $professionals = Professional::active()
            ->with(['specialty', 'appointments' => fn($q) =>
                $q->attended()->whereBetween('appointment_date', [$startDate, $endDate])
            ])
            ->ordered()
            ->when($professionalId, fn($q) => $q->where('id', $professionalId))
            ->get()
            ->map(fn($p) => [
                'id'           => $p->id,
                'full_name'    => $p->full_name,
                'specialty'    => $p->specialty?->name ?? '—',
                'appointments' => $p->appointments->count(),
                'total_amount' => $p->appointments->sum('final_amount'),
                'avg_amount'   => $p->appointments->count() > 0
                    ? $p->appointments->avg('final_amount') : 0,
                'by_month'     => $p->appointments
                    ->groupBy(fn($a) => $a->appointment_date->format('Y-m'))
                    ->map(fn($g) => ['count' => $g->count(), 'amount' => $g->sum('final_amount')])
                    ->sortKeys(),
            ])
            ->filter(fn($p) => $p['appointments'] > 0)
            ->sortByDesc('total_amount')
            ->values();

        $grandTotal = $professionals->sum('total_amount');
        $grandCount = $professionals->sum('appointments');

        // Meses presentes en los datos
        $months = $professionals->flatMap(fn($p) => array_keys($p['by_month']->toArray()))
            ->unique()->sort()->values();

        $allProfessionals = Professional::active()->ordered()->get();

        return view('reports.profesionales-ingresos', compact(
            'professionals', 'grandTotal', 'grandCount', 'months',
            'allProfessionals', 'dateFrom', 'dateTo', 'professionalId'
        ));
    }

    /**
     * Imprimir informe de ingresos por profesional
     */
    public function printProfesionalesIngresos(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo   = $request->get('date_to', now()->format('Y-m-d'));
        $professionalId = $request->get('professional_id');

        $startDate = Carbon::parse($dateFrom)->startOfDay();
        $endDate   = Carbon::parse($dateTo)->endOfDay();

        $professionals = Professional::active()
            ->with(['specialty', 'appointments' => fn($q) =>
                $q->attended()->whereBetween('appointment_date', [$startDate, $endDate])
            ])
            ->ordered()
            ->when($professionalId, fn($q) => $q->where('id', $professionalId))
            ->get()
            ->map(fn($p) => [
                'full_name'    => $p->full_name,
                'specialty'    => $p->specialty?->name ?? '—',
                'appointments' => $p->appointments->count(),
                'total_amount' => $p->appointments->sum('final_amount'),
                'avg_amount'   => $p->appointments->count() > 0
                    ? $p->appointments->avg('final_amount') : 0,
            ])
            ->filter(fn($p) => $p['appointments'] > 0)
            ->sortByDesc('total_amount')
            ->values();

        $grandTotal = $professionals->sum('total_amount');
        $grandCount = $professionals->sum('appointments');

        return view('reports.profesionales-ingresos-print', compact(
            'professionals', 'grandTotal', 'grandCount', 'dateFrom', 'dateTo'
        ));
    }

    /**
     * Informe de consultas por profesional (estados)
     */
    public function profesionalesConsultas(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo   = $request->get('date_to', now()->format('Y-m-d'));
        $professionalId = $request->get('professional_id');

        $startDate = Carbon::parse($dateFrom)->startOfDay();
        $endDate   = Carbon::parse($dateTo)->endOfDay();

        $professionals = Professional::active()
            ->with(['specialty', 'appointments' => fn($q) =>
                $q->whereBetween('appointment_date', [$startDate, $endDate])
                  ->whereIn('status', ['attended', 'absent', 'cancelled', 'scheduled'])
            ])
            ->ordered()
            ->when($professionalId, fn($q) => $q->where('id', $professionalId))
            ->get()
            ->map(function ($p) {
                $apps      = $p->appointments;
                $total     = $apps->count();
                $attended  = $apps->where('status', 'attended')->count();
                $absent    = $apps->where('status', 'absent')->count();
                $cancelled = $apps->where('status', 'cancelled')->count();
                $scheduled = $apps->where('status', 'scheduled')->count();
                $completed = $attended + $absent;
                return [
                    'full_name'       => $p->full_name,
                    'specialty'       => $p->specialty?->name ?? '—',
                    'total'           => $total,
                    'attended'        => $attended,
                    'absent'          => $absent,
                    'cancelled'       => $cancelled,
                    'scheduled'       => $scheduled,
                    'attendance_rate' => $completed > 0 ? round(($attended / $completed) * 100, 1) : 0,
                    'absence_rate'    => $completed > 0 ? round(($absent  / $completed) * 100, 1) : 0,
                ];
            })
            ->filter(fn($p) => $p['total'] > 0)
            ->sortByDesc('total')
            ->values();

        $globalTotal     = $professionals->sum('total');
        $globalAttended  = $professionals->sum('attended');
        $globalAbsent    = $professionals->sum('absent');
        $globalCancelled = $professionals->sum('cancelled');
        $globalScheduled = $professionals->sum('scheduled');
        $globalCompleted = $globalAttended + $globalAbsent;
        $globalRate      = $globalCompleted > 0 ? round(($globalAttended / $globalCompleted) * 100, 1) : 0;

        $allProfessionals = Professional::active()->ordered()->get();

        return view('reports.profesionales-consultas', compact(
            'professionals', 'globalTotal', 'globalAttended', 'globalAbsent',
            'globalCancelled', 'globalScheduled', 'globalRate',
            'allProfessionals', 'dateFrom', 'dateTo', 'professionalId'
        ));
    }

    public function printProfesionalesConsultas(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo   = $request->get('date_to', now()->format('Y-m-d'));
        $professionalId = $request->get('professional_id');

        $startDate = Carbon::parse($dateFrom)->startOfDay();
        $endDate   = Carbon::parse($dateTo)->endOfDay();

        $professionals = Professional::active()
            ->with(['specialty', 'appointments' => fn($q) =>
                $q->whereBetween('appointment_date', [$startDate, $endDate])
                  ->whereIn('status', ['attended', 'absent', 'cancelled', 'scheduled'])
            ])
            ->ordered()
            ->when($professionalId, fn($q) => $q->where('id', $professionalId))
            ->get()
            ->map(function ($p) {
                $apps      = $p->appointments;
                $total     = $apps->count();
                $attended  = $apps->where('status', 'attended')->count();
                $absent    = $apps->where('status', 'absent')->count();
                $cancelled = $apps->where('status', 'cancelled')->count();
                $scheduled = $apps->where('status', 'scheduled')->count();
                $completed = $attended + $absent;
                return [
                    'full_name'       => $p->full_name,
                    'specialty'       => $p->specialty?->name ?? '—',
                    'total'           => $total,
                    'attended'        => $attended,
                    'absent'          => $absent,
                    'cancelled'       => $cancelled,
                    'scheduled'       => $scheduled,
                    'attendance_rate' => $completed > 0 ? round(($attended / $completed) * 100, 1) : 0,
                    'absence_rate'    => $completed > 0 ? round(($absent  / $completed) * 100, 1) : 0,
                ];
            })
            ->filter(fn($p) => $p['total'] > 0)
            ->sortByDesc('total')
            ->values();

        $globalTotal     = $professionals->sum('total');
        $globalAttended  = $professionals->sum('attended');
        $globalAbsent    = $professionals->sum('absent');
        $globalCancelled = $professionals->sum('cancelled');
        $globalScheduled = $professionals->sum('scheduled');
        $globalCompleted = $globalAttended + $globalAbsent;
        $globalRate      = $globalCompleted > 0 ? round(($globalAttended / $globalCompleted) * 100, 1) : 0;

        return view('reports.profesionales-consultas-print', compact(
            'professionals', 'globalTotal', 'globalAttended', 'globalAbsent',
            'globalCancelled', 'globalScheduled', 'globalRate',
            'dateFrom', 'dateTo', 'professionalId'
        ));
    }

    /**
     * Informe de comisiones por profesional
     */
    public function profesionalesComisiones(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo   = $request->get('date_to', now()->format('Y-m-d'));
        $professionalId = $request->get('professional_id');

        $query = ProfessionalLiquidation::with(['professional.specialty'])
            ->whereBetween('liquidation_date', [$dateFrom, $dateTo]);

        if ($professionalId) {
            $query->where('professional_id', $professionalId);
        }

        $liquidations = $query->orderBy('liquidation_date')->get();

        $byProfessional = $liquidations->groupBy('professional_id')
            ->map(function ($group) {
                $pro = $group->first()->professional;
                return [
                    'full_name'           => $pro->full_name,
                    'specialty'           => $pro->specialty?->name ?? '—',
                    'commission_pct'      => $pro->commission_percentage,
                    'total_collected'     => $group->sum('total_collected'),
                    'professional_amount' => $group->sum('net_professional_amount'),
                    'clinic_amount'       => $group->sum('clinic_amount'),
                    'liquidations_count'  => $group->count(),
                    'by_month'            => $group->groupBy(fn($l) => $l->liquidation_date->format('Y-m'))
                        ->map(fn($g) => [
                            'total_collected'     => $g->sum('total_collected'),
                            'professional_amount' => $g->sum('net_professional_amount'),
                            'clinic_amount'       => $g->sum('clinic_amount'),
                        ])->sortKeys(),
                ];
            })
            ->sortByDesc('professional_amount')
            ->values();

        $totals = [
            'total_collected'     => $byProfessional->sum('total_collected'),
            'professional_amount' => $byProfessional->sum('professional_amount'),
            'clinic_amount'       => $byProfessional->sum('clinic_amount'),
            'liquidations_count'  => $byProfessional->sum('liquidations_count'),
        ];

        $months = $liquidations->map(fn($l) => $l->liquidation_date->format('Y-m'))
            ->unique()->sort()->values();

        $allProfessionals = Professional::active()->ordered()->get();

        return view('reports.profesionales-comisiones', compact(
            'byProfessional', 'totals', 'months',
            'allProfessionals', 'dateFrom', 'dateTo', 'professionalId'
        ));
    }

    /**
     * Imprimir informe de comisiones
     */
    public function printProfesionalesComisiones(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo   = $request->get('date_to', now()->format('Y-m-d'));
        $professionalId = $request->get('professional_id');

        $query = ProfessionalLiquidation::with(['professional.specialty'])
            ->whereBetween('liquidation_date', [$dateFrom, $dateTo]);

        if ($professionalId) {
            $query->where('professional_id', $professionalId);
        }

        $liquidations = $query->orderBy('liquidation_date')->get();

        $byProfessional = $liquidations->groupBy('professional_id')
            ->map(function ($group) {
                $pro = $group->first()->professional;
                return [
                    'full_name'           => $pro->full_name,
                    'specialty'           => $pro->specialty?->name ?? '—',
                    'commission_pct'      => $pro->commission_percentage,
                    'total_collected'     => $group->sum('total_collected'),
                    'professional_amount' => $group->sum('net_professional_amount'),
                    'clinic_amount'       => $group->sum('clinic_amount'),
                    'liquidations_count'  => $group->count(),
                ];
            })
            ->sortByDesc('professional_amount')
            ->values();

        $totals = [
            'total_collected'     => $byProfessional->sum('total_collected'),
            'professional_amount' => $byProfessional->sum('professional_amount'),
            'clinic_amount'       => $byProfessional->sum('clinic_amount'),
        ];

        return view('reports.profesionales-comisiones-print', compact(
            'byProfessional', 'totals', 'dateFrom', 'dateTo'
        ));
    }

    /**
     * Comparativa de profesionales (Chart.js)
     */
    public function profesionalesComparativa(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo   = $request->get('date_to', now()->format('Y-m-d'));

        $startDate = Carbon::parse($dateFrom)->startOfDay();
        $endDate   = Carbon::parse($dateTo)->endOfDay();

        // Counts por profesional y estado
        $appointmentStats = Appointment::whereBetween('appointment_date', [$startDate, $endDate])
            ->whereIn('status', ['attended', 'absent', 'cancelled', 'scheduled'])
            ->selectRaw('professional_id, status, COUNT(*) as cnt')
            ->groupBy('professional_id', 'status')
            ->get()
            ->groupBy('professional_id');

        // Montos de liquidaciones
        $commissionStats = ProfessionalLiquidation::whereBetween('liquidation_date', [$dateFrom, $dateTo])
            ->selectRaw('professional_id, SUM(net_professional_amount) as commission_total, SUM(total_collected) as billed_total, SUM(clinic_amount) as clinic_total')
            ->groupBy('professional_id')
            ->get()
            ->keyBy('professional_id');

        $professionals = Professional::active()->with('specialty')->ordered()->get()
            ->map(function ($pro) use ($appointmentStats, $commissionStats) {
                $stats   = $appointmentStats->get($pro->id, collect());
                $commRow = $commissionStats->get($pro->id);
                $attended  = $stats->where('status', 'attended')->sum('cnt');
                $absent    = $stats->where('status', 'absent')->sum('cnt');
                $cancelled = $stats->where('status', 'cancelled')->sum('cnt');
                $scheduled = $stats->where('status', 'scheduled')->sum('cnt');
                $completed = $attended + $absent;
                return [
                    'full_name'        => $pro->full_name,
                    'specialty'        => $pro->specialty?->name ?? '—',
                    'commission_pct'   => $pro->commission_percentage,
                    'appointments'     => $stats->sum('cnt'),
                    'attended'         => $attended,
                    'absent'           => $absent,
                    'cancelled'        => $cancelled,
                    'scheduled'        => $scheduled,
                    'attendance_rate'  => $completed > 0 ? round(($attended / $completed) * 100, 1) : 0,
                    'billed_total'     => $commRow?->billed_total ?? 0,
                    'commission_total' => $commRow?->commission_total ?? 0,
                    'clinic_total'     => $commRow?->clinic_total ?? 0,
                ];
            })
            ->filter(fn($p) => $p['appointments'] > 0 || $p['billed_total'] > 0)
            ->values();

        $chartData = [
            'labels'   => $professionals->pluck('full_name')->values(),
            'datasets' => [
                [
                    'label'           => 'Turnos Atendidos',
                    'data'            => $professionals->pluck('attended')->values(),
                    'backgroundColor' => 'rgba(16,185,129,0.7)',
                    'borderColor'     => 'rgb(16,185,129)',
                    'borderWidth'     => 1,
                    'yAxisID'         => 'y1',
                ],
                [
                    'label'           => 'Facturado ($)',
                    'data'            => $professionals->pluck('billed_total')->values(),
                    'backgroundColor' => 'rgba(59,130,246,0.7)',
                    'borderColor'     => 'rgb(59,130,246)',
                    'borderWidth'     => 1,
                    'yAxisID'         => 'y',
                ],
                [
                    'label'           => 'Comisión ($)',
                    'data'            => $professionals->pluck('commission_total')->values(),
                    'backgroundColor' => 'rgba(245,158,11,0.7)',
                    'borderColor'     => 'rgb(245,158,11)',
                    'borderWidth'     => 1,
                    'yAxisID'         => 'y',
                ],
            ],
        ];

        return view('reports.profesionales-comparativa', compact(
            'professionals', 'chartData', 'dateFrom', 'dateTo'
        ));
    }

    /**
     * Imprimir comparativa de profesionales
     */
    public function printProfesionalesComparativa(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo   = $request->get('date_to', now()->format('Y-m-d'));

        $startDate = Carbon::parse($dateFrom)->startOfDay();
        $endDate   = Carbon::parse($dateTo)->endOfDay();

        $appointmentStats = Appointment::whereBetween('appointment_date', [$startDate, $endDate])
            ->whereIn('status', ['attended', 'absent', 'cancelled', 'scheduled'])
            ->selectRaw('professional_id, status, COUNT(*) as cnt')
            ->groupBy('professional_id', 'status')
            ->get()
            ->groupBy('professional_id');

        $commissionStats = ProfessionalLiquidation::whereBetween('liquidation_date', [$dateFrom, $dateTo])
            ->selectRaw('professional_id, SUM(net_professional_amount) as commission_total, SUM(total_collected) as billed_total, SUM(clinic_amount) as clinic_total')
            ->groupBy('professional_id')
            ->get()
            ->keyBy('professional_id');

        $professionals = Professional::active()->with('specialty')->ordered()->get()
            ->map(function ($pro) use ($appointmentStats, $commissionStats) {
                $stats   = $appointmentStats->get($pro->id, collect());
                $commRow = $commissionStats->get($pro->id);
                $attended  = $stats->where('status', 'attended')->sum('cnt');
                $absent    = $stats->where('status', 'absent')->sum('cnt');
                $completed = $attended + $absent;
                return [
                    'full_name'        => $pro->full_name,
                    'specialty'        => $pro->specialty?->name ?? '—',
                    'commission_pct'   => $pro->commission_percentage,
                    'appointments'     => $stats->sum('cnt'),
                    'attended'         => $attended,
                    'absent'           => $absent,
                    'attendance_rate'  => $completed > 0 ? round(($attended / $completed) * 100, 1) : 0,
                    'billed_total'     => $commRow?->billed_total ?? 0,
                    'commission_total' => $commRow?->commission_total ?? 0,
                    'clinic_total'     => $commRow?->clinic_total ?? 0,
                ];
            })
            ->filter(fn($p) => $p['appointments'] > 0 || $p['billed_total'] > 0)
            ->values();

        return view('reports.profesionales-comparativa-print', compact(
            'professionals', 'dateFrom', 'dateTo'
        ));
    }

    /**
     * Tendencia de métodos de pago (Chart.js)
     */
    public function pagosTendencia(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->subMonths(5)->startOfMonth()->format('Y-m-d'));
        $dateTo   = $request->get('date_to', now()->format('Y-m-d'));

        $rows = DB::table('payment_details')
            ->join('payments', 'payment_details.payment_id', '=', 'payments.id')
            ->whereBetween('payments.payment_date', [
                Carbon::parse($dateFrom)->startOfDay(),
                Carbon::parse($dateTo)->endOfDay(),
            ])
            ->where('payments.status', 'confirmed')
            ->selectRaw("DATE_FORMAT(payments.payment_date, '%Y-%m') as month, payment_method, SUM(payment_details.amount) as total")
            ->groupBy('month', 'payment_method')
            ->orderBy('month')
            ->get();

        $months = $rows->pluck('month')->unique()->sort()->values();

        $cashData     = $months->mapWithKeys(fn($m) => [$m => 0.0])->toArray();
        $transferData = $months->mapWithKeys(fn($m) => [$m => 0.0])->toArray();
        $cardData     = $months->mapWithKeys(fn($m) => [$m => 0.0])->toArray();
        $qrData       = $months->mapWithKeys(fn($m) => [$m => 0.0])->toArray();

        foreach ($rows as $row) {
            if ($row->payment_method === 'cash')
                $cashData[$row->month] = (float) $row->total;
            elseif ($row->payment_method === 'transfer')
                $transferData[$row->month] = (float) $row->total;
            elseif (in_array($row->payment_method, ['debit_card', 'credit_card']))
                $cardData[$row->month] += (float) $row->total;
            elseif ($row->payment_method === 'qr')
                $qrData[$row->month] = (float) $row->total;
        }

        $labels = $months->map(fn($m) => Carbon::createFromFormat('Y-m', $m)->isoFormat('MMM YY'))->values();

        $chartData = [
            'labels'   => $labels,
            'datasets' => [
                ['label' => 'Efectivo',      'data' => array_values($cashData),     'backgroundColor' => 'rgba(16,185,129,0.7)',  'borderColor' => 'rgb(16,185,129)',  'borderWidth' => 1],
                ['label' => 'Transferencia', 'data' => array_values($transferData), 'backgroundColor' => 'rgba(59,130,246,0.7)',  'borderColor' => 'rgb(59,130,246)',  'borderWidth' => 1],
                ['label' => 'Tarjeta',       'data' => array_values($cardData),     'backgroundColor' => 'rgba(245,158,11,0.7)',  'borderColor' => 'rgb(245,158,11)',  'borderWidth' => 1],
                ['label' => 'QR',            'data' => array_values($qrData),       'backgroundColor' => 'rgba(139,92,246,0.7)',  'borderColor' => 'rgb(139,92,246)',  'borderWidth' => 1],
            ],
        ];

        $monthlyTable = $months->map(fn($m) => [
            'label'    => Carbon::createFromFormat('Y-m', $m)->isoFormat('MMMM YYYY'),
            'cash'     => $cashData[$m],
            'transfer' => $transferData[$m],
            'card'     => $cardData[$m],
            'qr'       => $qrData[$m],
            'total'    => $cashData[$m] + $transferData[$m] + $cardData[$m] + $qrData[$m],
        ]);

        $totals = [
            'cash'        => array_sum($cashData),
            'transfer'    => array_sum($transferData),
            'card'        => array_sum($cardData),
            'qr'          => array_sum($qrData),
            'grand_total' => array_sum($cashData) + array_sum($transferData) + array_sum($cardData) + array_sum($qrData),
        ];

        return view('reports.pagos-tendencia', compact(
            'chartData', 'monthlyTable', 'totals', 'dateFrom', 'dateTo'
        ));
    }

    /**
     * Ausentismo de pacientes por profesional
     */
    public function pacientesAusentismo(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo   = $request->get('date_to', now()->format('Y-m-d'));
        $professionalId = $request->get('professional_id');

        $startDate = Carbon::parse($dateFrom)->startOfDay();
        $endDate   = Carbon::parse($dateTo)->endOfDay();

        $rows = Appointment::whereBetween('appointment_date', [$startDate, $endDate])
            ->whereIn('status', ['attended', 'absent'])
            ->when($professionalId, fn($q) => $q->where('professional_id', $professionalId))
            ->selectRaw('professional_id, status, COUNT(*) as cnt')
            ->groupBy('professional_id', 'status')
            ->with('professional.specialty')
            ->get()
            ->groupBy('professional_id');

        $stats = $rows->map(function ($group) {
            $attended    = $group->where('status', 'attended')->sum('cnt');
            $absent      = $group->where('status', 'absent')->sum('cnt');
            $total       = $attended + $absent;
            $pro         = $group->first()->professional;
            return [
                'full_name'    => $pro->full_name,
                'specialty'    => $pro->specialty?->name ?? '—',
                'attended'     => $attended,
                'absent'       => $absent,
                'total'        => $total,
                'absence_rate' => $total > 0 ? round(($absent / $total) * 100, 1) : 0,
            ];
        })
        ->sortByDesc('absence_rate')
        ->values();

        $globalAbsent = $stats->sum('absent');
        $globalTotal  = $stats->sum('total');
        $globalRate   = $globalTotal > 0 ? round(($globalAbsent / $globalTotal) * 100, 1) : 0;

        $allProfessionals = Professional::active()->ordered()->get();

        return view('reports.pacientes-ausentismo', compact(
            'stats', 'globalAbsent', 'globalTotal', 'globalRate',
            'allProfessionals', 'dateFrom', 'dateTo', 'professionalId'
        ));
    }

    /**
     * Tasa de retención de pacientes
     */
    public function pacientesRetencion(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo   = $request->get('date_to', now()->format('Y-m-d'));

        $startDate = Carbon::parse($dateFrom)->startOfDay();
        $endDate   = Carbon::parse($dateTo)->endOfDay();

        $allInPeriod = Appointment::attended()
            ->whereBetween('appointment_date', [$startDate, $endDate])
            ->selectRaw('patient_id, COUNT(*) as visits')
            ->groupBy('patient_id')
            ->get();

        $totalUnique  = $allInPeriod->count();
        $returning    = $allInPeriod->where('visits', '>', 1)->count();
        $singleVisit  = $totalUnique - $returning;
        $retentionRate = $totalUnique > 0 ? round(($returning / $totalUnique) * 100, 1) : 0;

        // Nuevos: pacientes cuya primera cita atendida EVER cae en este período
        $newPatients = Appointment::attended()
            ->whereBetween('appointment_date', [$startDate, $endDate])
            ->whereDoesntHave('patient', fn($q) =>
                $q->whereHas('appointments', fn($a) =>
                    $a->attended()->where('appointment_date', '<', $startDate)
                )
            )
            ->distinct('patient_id')
            ->count('patient_id');

        $recurringPatients = $totalUnique - $newPatients;

        // Distribución de visitas
        $visitDistribution = $allInPeriod->groupBy('visits')
            ->map(fn($g) => $g->count())
            ->sortKeys();

        return view('reports.pacientes-retencion', compact(
            'totalUnique', 'returning', 'singleVisit', 'retentionRate',
            'newPatients', 'recurringPatients', 'visitDistribution',
            'dateFrom', 'dateTo'
        ));
    }

    /**
     * Frecuencia de visitas de pacientes
     */
    public function pacientesFrecuencia(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->subMonths(2)->startOfMonth()->format('Y-m-d'));
        $dateTo   = $request->get('date_to', now()->format('Y-m-d'));

        $startDate = Carbon::parse($dateFrom)->startOfDay();
        $endDate   = Carbon::parse($dateTo)->endOfDay();

        $patientVisits = Appointment::attended()
            ->whereBetween('appointment_date', [$startDate, $endDate])
            ->select('patient_id', 'appointment_date')
            ->orderBy('patient_id')
            ->orderBy('appointment_date')
            ->get()
            ->groupBy('patient_id');

        $intervals = [];
        foreach ($patientVisits as $visits) {
            if ($visits->count() < 2) continue;
            $sorted = $visits->pluck('appointment_date')->sort()->values();
            $diffs  = [];
            for ($i = 1; $i < $sorted->count(); $i++) {
                $diffs[] = $sorted[$i]->diffInDays($sorted[$i - 1]);
            }
            $intervals[] = round(array_sum($diffs) / count($diffs));
        }

        $buckets = ['1-7 días' => 0, '8-14 días' => 0, '15-30 días' => 0, '31-60 días' => 0, '> 60 días' => 0];
        foreach ($intervals as $avg) {
            if ($avg <= 7)       $buckets['1-7 días']++;
            elseif ($avg <= 14)  $buckets['8-14 días']++;
            elseif ($avg <= 30)  $buckets['15-30 días']++;
            elseif ($avg <= 60)  $buckets['31-60 días']++;
            else                 $buckets['> 60 días']++;
        }

        $globalAvg        = count($intervals) > 0 ? round(array_sum($intervals) / count($intervals)) : null;
        $patientsWithMultiple = count($intervals);
        $totalPatients    = $patientVisits->count();

        return view('reports.pacientes-frecuencia', compact(
            'buckets', 'globalAvg', 'patientsWithMultiple', 'totalPatients',
            'dateFrom', 'dateTo'
        ));
    }

    /**
     * Pacientes nuevos vs viejos por mes (Chart.js)
     */
    public function pacientesNuevosViejos(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->subMonths(5)->startOfMonth()->format('Y-m-d'));
        $dateTo   = $request->get('date_to', now()->format('Y-m-d'));

        // Obtener primera cita de cada paciente (ever)
        $firstVisits = Appointment::attended()
            ->selectRaw('patient_id, MIN(appointment_date) as first_visit')
            ->groupBy('patient_id')
            ->pluck('first_visit', 'patient_id');

        $months = collect();
        $current = Carbon::parse($dateFrom)->startOfMonth();
        $end     = Carbon::parse($dateTo)->endOfMonth();

        while ($current->lessThanOrEqualTo($end)) {
            $monthStart = $current->copy()->startOfMonth();
            $monthEnd   = $current->copy()->endOfMonth();

            $patientsThisMonth = Appointment::attended()
                ->whereBetween('appointment_date', [$monthStart->startOfDay(), $monthEnd->endOfDay()])
                ->distinct()
                ->pluck('patient_id');

            $newCount = $patientsThisMonth->filter(
                fn($pid) => isset($firstVisits[$pid])
                    && Carbon::parse($firstVisits[$pid])->between($monthStart, $monthEnd)
            )->count();

            $returningCount = $patientsThisMonth->count() - $newCount;

            $months->push([
                'label'     => $current->isoFormat('MMM YY'),
                'month_key' => $current->format('Y-m'),
                'new'       => max(0, $newCount),
                'returning' => max(0, $returningCount),
                'total'     => $patientsThisMonth->count(),
            ]);

            $current->addMonth();
        }

        $chartData = [
            'labels'   => $months->pluck('label')->values(),
            'datasets' => [
                [
                    'label'           => 'Nuevos',
                    'data'            => $months->pluck('new')->values(),
                    'backgroundColor' => 'rgba(16,185,129,0.8)',
                    'borderColor'     => 'rgb(16,185,129)',
                    'borderWidth'     => 1,
                ],
                [
                    'label'           => 'Volvieron',
                    'data'            => $months->pluck('returning')->values(),
                    'backgroundColor' => 'rgba(59,130,246,0.8)',
                    'borderColor'     => 'rgb(59,130,246)',
                    'borderWidth'     => 1,
                ],
            ],
        ];

        $totals = [
            'new'       => $months->sum('new'),
            'returning' => $months->sum('returning'),
            'total'     => $months->sum('total'),
        ];

        return view('reports.pacientes-nuevos-viejos', compact(
            'months', 'chartData', 'totals', 'dateFrom', 'dateTo'
        ));
    }

    /**
     * Ingresos por obra social / financiador
     */
    public function ingresosObraSocial(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo   = $request->get('date_to', now()->format('Y-m-d'));

        $startDate = Carbon::parse($dateFrom)->startOfDay();
        $endDate   = Carbon::parse($dateTo)->endOfDay();

        $appointments = Appointment::attended()
            ->whereBetween('appointment_date', [$startDate, $endDate])
            ->with('patient:id,health_insurance')
            ->get();

        $byInsurance = $appointments
            ->groupBy(fn($a) => strtolower(trim($a->patient?->health_insurance ?? '')) ?: 'sin obra social')
            ->map(fn($g, $key) => [
                'name'    => $g->first()->patient?->health_insurance ?: 'Sin obra social',
                'count'   => $g->count(),
                'amount'  => $g->sum('final_amount'),
                'avg'     => $g->avg('final_amount'),
            ])
            ->sortByDesc('amount')
            ->values();

        $totals = [
            'count'  => $appointments->count(),
            'amount' => $appointments->sum('final_amount'),
        ];

        return view('reports.ingresos-obra-social', compact(
            'byInsurance', 'totals', 'dateFrom', 'dateTo'
        ));
    }

    /**
     * Cobros pendientes (turnos atendidos sin pago)
     */
    public function cobrosPendientes(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->subDays(30)->format('Y-m-d'));
        $dateTo   = $request->get('date_to', now()->format('Y-m-d'));
        $professionalId = $request->get('professional_id');

        $startDate = Carbon::parse($dateFrom)->startOfDay();
        $endDate   = Carbon::parse($dateTo)->endOfDay();

        $pending = Appointment::unpaid()
            ->whereBetween('appointment_date', [$startDate, $endDate])
            ->with(['patient:id,first_name,last_name,phone', 'professional:id,first_name,last_name'])
            ->when($professionalId, fn($q) => $q->where('professional_id', $professionalId))
            ->orderBy('appointment_date', 'desc')
            ->get();

        $totals = [
            'count'           => $pending->count(),
            'estimated_total' => $pending->sum('estimated_amount'),
        ];

        $byProfessional = $pending->groupBy('professional_id')
            ->map(fn($g) => ['count' => $g->count(), 'amount' => $g->sum('estimated_amount')])
            ->sortByDesc('count')
            ->values();

        $allProfessionals = Professional::active()->ordered()->get();

        return view('reports.cobros-pendientes', compact(
            'pending', 'totals', 'byProfessional',
            'allProfessionals', 'dateFrom', 'dateTo', 'professionalId'
        ));
    }

    /**
     * Flujo mensual de caja (Chart.js)
     */
    public function flujoCajaMensual(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->subMonths(5)->startOfMonth()->format('Y-m-d'));
        $dateTo   = $request->get('date_to', now()->format('Y-m-d'));

        $start = Carbon::parse($dateFrom)->startOfDay();
        $end = Carbon::parse($dateTo)->endOfDay();

        $rows = DB::table('cash_movements')
            ->join('movement_types', 'cash_movements.movement_type_id', '=', 'movement_types.id')
            ->whereNotIn('movement_types.code', ['cash_opening', 'cash_closing'])
            ->whereBetween('cash_movements.created_at', [$start, $end])
            ->selectRaw("
                DATE_FORMAT(cash_movements.created_at, '%Y-%m') as month,
                SUM(CASE WHEN cash_movements.amount > 0 THEN cash_movements.amount ELSE 0 END) as income,
                SUM(CASE WHEN cash_movements.amount < 0 THEN ABS(cash_movements.amount) ELSE 0 END) as expenses
            ")
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $externalRows = Expense::query()
            ->whereBetween('expense_date', [$start->toDateString(), $end->toDateString()])
            ->selectRaw("DATE_FORMAT(expense_date, '%Y-%m') as month, SUM(amount) as expenses")
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $cashMap = $rows->mapWithKeys(fn ($r) => [
            $r->month => [
                'income' => (float) $r->income,
                'expenses' => (float) $r->expenses,
            ],
        ])->toArray();

        $externalMap = $externalRows->mapWithKeys(fn ($r) => [
            $r->month => (float) $r->expenses,
        ])->toArray();

        $normalized = collect();
        $cursor = $start->copy()->startOfMonth();
        $endCursor = $end->copy()->startOfMonth();
        while ($cursor <= $endCursor) {
            $key = $cursor->format('Y-m');
            $income = $cashMap[$key]['income'] ?? 0.0;
            $expenses = ($cashMap[$key]['expenses'] ?? 0.0) + ($externalMap[$key] ?? 0.0);

            $normalized->push((object) [
                'month' => $key,
                'income' => $income,
                'expenses' => $expenses,
            ]);

            $cursor->addMonth();
        }

        $chartData = [
            'labels'   => $normalized->map(fn($r) => Carbon::createFromFormat('Y-m', $r->month)->isoFormat('MMM YY'))->values(),
            'datasets' => [
                [
                    'label'           => 'Ingresos',
                    'data'            => $normalized->pluck('income')->values(),
                    'backgroundColor' => 'rgba(16,185,129,0.7)',
                    'borderColor'     => 'rgb(16,185,129)',
                    'borderWidth'     => 1,
                ],
                [
                    'label'           => 'Egresos',
                    'data'            => $normalized->pluck('expenses')->values(),
                    'backgroundColor' => 'rgba(239,68,68,0.7)',
                    'borderColor'     => 'rgb(239,68,68)',
                    'borderWidth'     => 1,
                ],
            ],
        ];

        $monthly = $normalized->map(fn($r) => [
            'label'    => Carbon::createFromFormat('Y-m', $r->month)->isoFormat('MMMM YYYY'),
            'income'   => (float) $r->income,
            'expenses' => (float) $r->expenses,
            'balance'  => (float) $r->income - (float) $r->expenses,
        ]);

        $totals = [
            'income'   => $monthly->sum('income'),
            'expenses' => $monthly->sum('expenses'),
            'balance'  => $monthly->sum('balance'),
        ];

        return view('reports.flujo-caja-mensual', compact(
            'chartData', 'monthly', 'totals', 'dateFrom', 'dateTo'
        ));
    }

    /**
     * Imprimir tendencia de métodos de pago
     */
    public function printPagosTendencia(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->subMonths(5)->startOfMonth()->format('Y-m-d'));
        $dateTo   = $request->get('date_to', now()->format('Y-m-d'));

        $rows = DB::table('payment_details')
            ->join('payments', 'payment_details.payment_id', '=', 'payments.id')
            ->whereBetween('payments.payment_date', [
                Carbon::parse($dateFrom)->startOfDay(),
                Carbon::parse($dateTo)->endOfDay(),
            ])
            ->where('payments.status', 'confirmed')
            ->selectRaw("DATE_FORMAT(payments.payment_date, '%Y-%m') as month, payment_method, SUM(payment_details.amount) as total")
            ->groupBy('month', 'payment_method')
            ->orderBy('month')
            ->get();

        $months = $rows->pluck('month')->unique()->sort()->values();

        $cashData     = $months->mapWithKeys(fn($m) => [$m => 0.0])->toArray();
        $transferData = $months->mapWithKeys(fn($m) => [$m => 0.0])->toArray();
        $cardData     = $months->mapWithKeys(fn($m) => [$m => 0.0])->toArray();
        $qrData       = $months->mapWithKeys(fn($m) => [$m => 0.0])->toArray();

        foreach ($rows as $row) {
            if ($row->payment_method === 'cash')
                $cashData[$row->month] = (float) $row->total;
            elseif ($row->payment_method === 'transfer')
                $transferData[$row->month] = (float) $row->total;
            elseif (in_array($row->payment_method, ['debit_card', 'credit_card']))
                $cardData[$row->month] += (float) $row->total;
            elseif ($row->payment_method === 'qr')
                $qrData[$row->month] = (float) $row->total;
        }

        $monthlyTable = $months->map(fn($m) => [
            'label'    => Carbon::createFromFormat('Y-m', $m)->isoFormat('MMMM YYYY'),
            'cash'     => $cashData[$m],
            'transfer' => $transferData[$m],
            'card'     => $cardData[$m],
            'qr'       => $qrData[$m],
            'total'    => $cashData[$m] + $transferData[$m] + $cardData[$m] + $qrData[$m],
        ]);

        $totals = [
            'cash'        => array_sum($cashData),
            'transfer'    => array_sum($transferData),
            'card'        => array_sum($cardData),
            'qr'          => array_sum($qrData),
            'grand_total' => array_sum($cashData) + array_sum($transferData) + array_sum($cardData) + array_sum($qrData),
        ];

        return view('reports.pagos-tendencia-print', compact(
            'monthlyTable', 'totals', 'dateFrom', 'dateTo'
        ));
    }

    /**
     * Imprimir ausentismo de pacientes
     */
    public function printPacientesAusentismo(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo   = $request->get('date_to', now()->format('Y-m-d'));
        $professionalId = $request->get('professional_id');

        $startDate = Carbon::parse($dateFrom)->startOfDay();
        $endDate   = Carbon::parse($dateTo)->endOfDay();

        $rows = Appointment::whereBetween('appointment_date', [$startDate, $endDate])
            ->whereIn('status', ['attended', 'absent'])
            ->when($professionalId, fn($q) => $q->where('professional_id', $professionalId))
            ->selectRaw('professional_id, status, COUNT(*) as cnt')
            ->groupBy('professional_id', 'status')
            ->with('professional.specialty')
            ->get()
            ->groupBy('professional_id');

        $stats = $rows->map(function ($group) {
            $attended    = $group->where('status', 'attended')->sum('cnt');
            $absent      = $group->where('status', 'absent')->sum('cnt');
            $total       = $attended + $absent;
            $pro         = $group->first()->professional;
            return [
                'full_name'    => $pro->full_name,
                'specialty'    => $pro->specialty?->name ?? '—',
                'attended'     => $attended,
                'absent'       => $absent,
                'total'        => $total,
                'absence_rate' => $total > 0 ? round(($absent / $total) * 100, 1) : 0,
            ];
        })
        ->sortByDesc('absence_rate')
        ->values();

        $globalAbsent = $stats->sum('absent');
        $globalTotal  = $stats->sum('total');
        $globalRate   = $globalTotal > 0 ? round(($globalAbsent / $globalTotal) * 100, 1) : 0;

        return view('reports.pacientes-ausentismo-print', compact(
            'stats', 'globalAbsent', 'globalTotal', 'globalRate',
            'dateFrom', 'dateTo'
        ));
    }

    /**
     * Imprimir retención de pacientes
     */
    public function printPacientesRetencion(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo   = $request->get('date_to', now()->format('Y-m-d'));

        $startDate = Carbon::parse($dateFrom)->startOfDay();
        $endDate   = Carbon::parse($dateTo)->endOfDay();

        $allInPeriod = Appointment::attended()
            ->whereBetween('appointment_date', [$startDate, $endDate])
            ->selectRaw('patient_id, COUNT(*) as visits')
            ->groupBy('patient_id')
            ->get();

        $totalUnique   = $allInPeriod->count();
        $returning     = $allInPeriod->where('visits', '>', 1)->count();
        $singleVisit   = $totalUnique - $returning;
        $retentionRate = $totalUnique > 0 ? round(($returning / $totalUnique) * 100, 1) : 0;

        $newPatients = Appointment::attended()
            ->whereBetween('appointment_date', [$startDate, $endDate])
            ->whereDoesntHave('patient', fn($q) =>
                $q->whereHas('appointments', fn($a) =>
                    $a->attended()->where('appointment_date', '<', $startDate)
                )
            )
            ->distinct('patient_id')
            ->count('patient_id');

        $recurringPatients = $totalUnique - $newPatients;

        $visitDistribution = $allInPeriod->groupBy('visits')
            ->map(fn($g) => $g->count())
            ->sortKeys();

        return view('reports.pacientes-retencion-print', compact(
            'totalUnique', 'returning', 'singleVisit', 'retentionRate',
            'newPatients', 'recurringPatients', 'visitDistribution',
            'dateFrom', 'dateTo'
        ));
    }

    /**
     * Imprimir frecuencia de visitas de pacientes
     */
    public function printPacientesFrecuencia(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->subMonths(2)->startOfMonth()->format('Y-m-d'));
        $dateTo   = $request->get('date_to', now()->format('Y-m-d'));

        $startDate = Carbon::parse($dateFrom)->startOfDay();
        $endDate   = Carbon::parse($dateTo)->endOfDay();

        $patientVisits = Appointment::attended()
            ->whereBetween('appointment_date', [$startDate, $endDate])
            ->select('patient_id', 'appointment_date')
            ->orderBy('patient_id')
            ->orderBy('appointment_date')
            ->get()
            ->groupBy('patient_id');

        $intervals = [];
        foreach ($patientVisits as $visits) {
            if ($visits->count() < 2) continue;
            $sorted = $visits->pluck('appointment_date')->sort()->values();
            $diffs  = [];
            for ($i = 1; $i < $sorted->count(); $i++) {
                $diffs[] = $sorted[$i]->diffInDays($sorted[$i - 1]);
            }
            $intervals[] = round(array_sum($diffs) / count($diffs));
        }

        $buckets = ['1-7 días' => 0, '8-14 días' => 0, '15-30 días' => 0, '31-60 días' => 0, '> 60 días' => 0];
        foreach ($intervals as $avg) {
            if ($avg <= 7)       $buckets['1-7 días']++;
            elseif ($avg <= 14)  $buckets['8-14 días']++;
            elseif ($avg <= 30)  $buckets['15-30 días']++;
            elseif ($avg <= 60)  $buckets['31-60 días']++;
            else                 $buckets['> 60 días']++;
        }

        $globalAvg            = count($intervals) > 0 ? round(array_sum($intervals) / count($intervals)) : null;
        $patientsWithMultiple = count($intervals);
        $totalPatients        = $patientVisits->count();

        return view('reports.pacientes-frecuencia-print', compact(
            'buckets', 'globalAvg', 'patientsWithMultiple', 'totalPatients',
            'dateFrom', 'dateTo'
        ));
    }

    /**
     * Imprimir pacientes nuevos vs viejos
     */
    public function printPacientesNuevosViejos(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->subMonths(5)->startOfMonth()->format('Y-m-d'));
        $dateTo   = $request->get('date_to', now()->format('Y-m-d'));

        $firstVisits = Appointment::attended()
            ->selectRaw('patient_id, MIN(appointment_date) as first_visit')
            ->groupBy('patient_id')
            ->pluck('first_visit', 'patient_id');

        $months  = collect();
        $current = Carbon::parse($dateFrom)->startOfMonth();
        $end     = Carbon::parse($dateTo)->endOfMonth();

        while ($current->lessThanOrEqualTo($end)) {
            $monthStart = $current->copy()->startOfMonth();
            $monthEnd   = $current->copy()->endOfMonth();

            $patientsThisMonth = Appointment::attended()
                ->whereBetween('appointment_date', [$monthStart->startOfDay(), $monthEnd->endOfDay()])
                ->distinct()
                ->pluck('patient_id');

            $newCount = $patientsThisMonth->filter(
                fn($pid) => isset($firstVisits[$pid])
                    && Carbon::parse($firstVisits[$pid])->between($monthStart, $monthEnd)
            )->count();

            $months->push([
                'label'     => $current->isoFormat('MMMM YYYY'),
                'new'       => max(0, $newCount),
                'returning' => max(0, $patientsThisMonth->count() - $newCount),
                'total'     => $patientsThisMonth->count(),
            ]);

            $current->addMonth();
        }

        $totals = [
            'new'       => $months->sum('new'),
            'returning' => $months->sum('returning'),
            'total'     => $months->sum('total'),
        ];

        return view('reports.pacientes-nuevos-viejos-print', compact(
            'months', 'totals', 'dateFrom', 'dateTo'
        ));
    }

    /**
     * Imprimir ingresos por obra social
     */
    public function printIngresosObraSocial(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo   = $request->get('date_to', now()->format('Y-m-d'));

        $startDate = Carbon::parse($dateFrom)->startOfDay();
        $endDate   = Carbon::parse($dateTo)->endOfDay();

        $appointments = Appointment::attended()
            ->whereBetween('appointment_date', [$startDate, $endDate])
            ->with('patient:id,health_insurance')
            ->get();

        $byInsurance = $appointments
            ->groupBy(fn($a) => strtolower(trim($a->patient?->health_insurance ?? '')) ?: 'sin obra social')
            ->map(fn($g) => [
                'name'   => $g->first()->patient?->health_insurance ?: 'Sin obra social',
                'count'  => $g->count(),
                'amount' => $g->sum('final_amount'),
                'avg'    => $g->avg('final_amount'),
            ])
            ->sortByDesc('amount')
            ->values();

        $totals = [
            'count'  => $appointments->count(),
            'amount' => $appointments->sum('final_amount'),
        ];

        return view('reports.ingresos-obra-social-print', compact(
            'byInsurance', 'totals', 'dateFrom', 'dateTo'
        ));
    }

    /**
     * Imprimir cobros pendientes
     */
    public function printCobrosPendientes(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->subDays(30)->format('Y-m-d'));
        $dateTo   = $request->get('date_to', now()->format('Y-m-d'));
        $professionalId = $request->get('professional_id');

        $startDate = Carbon::parse($dateFrom)->startOfDay();
        $endDate   = Carbon::parse($dateTo)->endOfDay();

        $pending = Appointment::unpaid()
            ->whereBetween('appointment_date', [$startDate, $endDate])
            ->with(['patient:id,first_name,last_name,phone', 'professional:id,first_name,last_name'])
            ->when($professionalId, fn($q) => $q->where('professional_id', $professionalId))
            ->orderBy('appointment_date', 'desc')
            ->get();

        $totals = [
            'count'           => $pending->count(),
            'estimated_total' => $pending->sum('estimated_amount'),
        ];

        $byProfessional = $pending->groupBy('professional_id')
            ->map(fn($g) => [
                'name'   => $g->first()->professional?->full_name ?? '—',
                'count'  => $g->count(),
                'amount' => $g->sum('estimated_amount'),
            ])
            ->sortByDesc('count')
            ->values();

        return view('reports.cobros-pendientes-print', compact(
            'pending', 'totals', 'byProfessional', 'dateFrom', 'dateTo'
        ));
    }

    /**
     * Imprimir flujo mensual de caja
     */
    public function printFlujoCajaMensual(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->subMonths(5)->startOfMonth()->format('Y-m-d'));
        $dateTo   = $request->get('date_to', now()->format('Y-m-d'));

        $rows = DB::table('cash_movements')
            ->join('movement_types', 'cash_movements.movement_type_id', '=', 'movement_types.id')
            ->whereNotIn('movement_types.code', ['cash_opening', 'cash_closing'])
            ->whereBetween('cash_movements.created_at', [
                Carbon::parse($dateFrom)->startOfDay(),
                Carbon::parse($dateTo)->endOfDay(),
            ])
            ->selectRaw("
                DATE_FORMAT(cash_movements.created_at, '%Y-%m') as month,
                SUM(CASE WHEN cash_movements.amount > 0 THEN cash_movements.amount ELSE 0 END) as income,
                SUM(CASE WHEN cash_movements.amount < 0 THEN ABS(cash_movements.amount) ELSE 0 END) as expenses
            ")
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $monthly = $rows->map(fn($r) => [
            'label'    => Carbon::createFromFormat('Y-m', $r->month)->isoFormat('MMMM YYYY'),
            'income'   => (float) $r->income,
            'expenses' => (float) $r->expenses,
            'balance'  => (float) $r->income - (float) $r->expenses,
        ]);

        $totals = [
            'income'   => $monthly->sum('income'),
            'expenses' => $monthly->sum('expenses'),
            'balance'  => $monthly->sum('balance'),
        ];

        return view('reports.flujo-caja-mensual-print', compact(
            'monthly', 'totals', 'dateFrom', 'dateTo'
        ));
    }

    /**
     * Etiquetas de estado de citas
     */
    private function getStatusLabel($status)
    {
        return match ($status) {
            'attended' => 'Atendido',
            'scheduled' => 'Programado',
            'cancelled' => 'Cancelado',
            'absent' => 'Ausente',
            default => 'Desconocido'
        };
    }

    /**
     * Análisis de Caja por período (vista web)
     */
    public function cashAnalysis(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));
        $groupBy = $request->get('group_by', 'day');

        $startDate = Carbon::parse($dateFrom);
        $endDate = Carbon::parse($dateTo);

        $movements = CashMovement::with(['user', 'movementType'])
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->orderBy('created_at')
            ->get();

        $movementsForTotals = $movements->filter(function ($movement) {
            return ! in_array($movement->movementType?->code, ['cash_opening', 'cash_closing']);
        });

        $includeExternal = $request->boolean('include_external');
        if ($includeExternal) {
            $movementsForTotals = $movementsForTotals->concat(
                $this->buildExternalExpensesAsMovements($startDate, $endDate)
            );
        }

        $reportData = $this->generateCashAnalysisData($movementsForTotals, $groupBy, $startDate, $endDate);

        $summary = [
            'total_inflows' => $movementsForTotals->where('amount', '>', 0)->sum('amount'),
            'total_outflows' => abs($movementsForTotals->where('amount', '<', 0)->sum('amount')),
            'net_amount' => $movementsForTotals->sum('amount'),
            'movements_count' => $movementsForTotals->count(),
            'period_days' => $startDate->diffInDays($endDate) + 1,
        ];

        $movementsByType = $movementsForTotals->groupBy(function ($movement) {
            return $movement->movementType?->code ?? 'unknown';
        })->map(function ($group, $typeCode) {
            $firstMovement = $group->first();
            return [
                'type' => $typeCode,
                'type_name' => $firstMovement->movementType?->name ?? ucfirst($typeCode),
                'icon' => $firstMovement->movementType?->icon ?? '📋',
                'inflows' => $group->where('amount', '>', 0)->sum('amount'),
                'outflows' => abs($group->where('amount', '<', 0)->sum('amount')),
                'count' => $group->count(),
            ];
        });

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'report_data' => $reportData,
                'summary' => $summary,
                'movements_by_type' => $movementsByType,
            ]);
        }

        return view('reports.cash-analysis', compact('reportData', 'summary', 'movementsByType', 'includeExternal'));
    }

    /**
     * Análisis de Caja — vista de impresión
     */
    public function printCashAnalysis(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));
        $groupBy = $request->get('group_by', 'day');

        $startDate = Carbon::parse($dateFrom);
        $endDate = Carbon::parse($dateTo);

        $movements = CashMovement::with(['user', 'movementType'])
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->orderBy('created_at')
            ->get();

        $movementsForTotals = $movements->filter(function ($movement) {
            return ! in_array($movement->movementType?->code, ['cash_opening', 'cash_closing']);
        });

        $includeExternal = $request->boolean('include_external');
        if ($includeExternal) {
            $movementsForTotals = $movementsForTotals->concat(
                $this->buildExternalExpensesAsMovements($startDate, $endDate)
            );
        }

        $reportData = $this->generateCashAnalysisData($movementsForTotals, $groupBy, $startDate, $endDate);

        $summary = [
            'total_inflows' => $movementsForTotals->where('amount', '>', 0)->sum('amount'),
            'total_outflows' => abs($movementsForTotals->where('amount', '<', 0)->sum('amount')),
            'net_amount' => $movementsForTotals->sum('amount'),
            'movements_count' => $movementsForTotals->count(),
            'period_days' => $startDate->diffInDays($endDate) + 1,
        ];

        $movementsByType = $movementsForTotals->groupBy(function ($movement) {
            return $movement->movementType?->code ?? 'unknown';
        })->map(function ($group, $typeCode) {
            $firstMovement = $group->first();
            return [
                'type' => $typeCode,
                'type_name' => $firstMovement->movementType?->name ?? ucfirst($typeCode),
                'icon' => $firstMovement->movementType?->icon ?? '📋',
                'inflows' => $group->where('amount', '>', 0)->sum('amount'),
                'outflows' => abs($group->where('amount', '<', 0)->sum('amount')),
                'count' => $group->count(),
            ];
        });

        return view('reports.cash-analysis-print', compact(
            'reportData', 'summary', 'movementsByType', 'dateFrom', 'dateTo', 'groupBy', 'includeExternal'
        ));
    }

    /**
     * Análisis de Caja — exportación CSV
     */
    public function exportCashAnalysisCsv(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));
        $groupBy = $request->get('group_by', 'day');

        $startDate = Carbon::parse($dateFrom);
        $endDate = Carbon::parse($dateTo);

        $movements = CashMovement::with(['user', 'movementType'])
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->orderBy('created_at')
            ->get();

        $movementsForTotals = $movements->filter(function ($movement) {
            return ! in_array($movement->movementType?->code, ['cash_opening', 'cash_closing']);
        });

        $includeExternal = $request->boolean('include_external');
        if ($includeExternal) {
            $movementsForTotals = $movementsForTotals->concat(
                $this->buildExternalExpensesAsMovements($startDate, $endDate)
            );
        }

        $reportData = $this->generateCashAnalysisData($movementsForTotals, $groupBy, $startDate, $endDate);

        $summary = [
            'total_inflows' => $movementsForTotals->where('amount', '>', 0)->sum('amount'),
            'total_outflows' => abs($movementsForTotals->where('amount', '<', 0)->sum('amount')),
            'net_amount' => $movementsForTotals->sum('amount'),
            'movements_count' => $movementsForTotals->count(),
            'period_days' => $startDate->diffInDays($endDate) + 1,
        ];

        $movementsByType = $movementsForTotals->groupBy(function ($movement) {
            return $movement->movementType?->code ?? 'unknown';
        })->map(function ($group, $typeCode) {
            $firstMovement = $group->first();
            return [
                'type' => $typeCode,
                'type_name' => $firstMovement->movementType?->name ?? ucfirst($typeCode),
                'inflows' => $group->where('amount', '>', 0)->sum('amount'),
                'outflows' => abs($group->where('amount', '<', 0)->sum('amount')),
                'count' => $group->count(),
            ];
        });

        $filename = 'reporte-caja-' . $dateFrom . '-a-' . $dateTo . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($summary, $reportData, $movementsByType, $dateFrom, $dateTo, $includeExternal) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($file, ['REPORTE DE CAJA'], ';');
            fputcsv($file, ["Periodo: $dateFrom al $dateTo"], ';');
            fputcsv($file, [], ';');

            fputcsv($file, ['RESUMEN'], ';');
            fputcsv($file, ['Total Ingresos', number_format($summary['total_inflows'], 2, ',', '.')], ';');
            fputcsv($file, ['Total Egresos', number_format($summary['total_outflows'], 2, ',', '.')], ';');
            fputcsv($file, ['Resultado Neto', number_format($summary['net_amount'], 2, ',', '.')], ';');
            fputcsv($file, ['Cantidad de Movimientos', $summary['movements_count']], ';');
            fputcsv($file, ['Dias del Periodo', $summary['period_days']], ';');
            if ($includeExternal) {
                fputcsv($file, ['Incluye gastos externos', 'Sí'], ';');
            }
            fputcsv($file, [], ';');

            fputcsv($file, ['DETALLE POR PERIODO'], ';');
            fputcsv($file, ['Periodo', 'Ingresos', 'Egresos', 'Neto', 'Movimientos'], ';');
            foreach ($reportData as $period) {
                fputcsv($file, [
                    $period['period_label'],
                    number_format($period['inflows'], 2, ',', '.'),
                    number_format($period['outflows'], 2, ',', '.'),
                    number_format($period['net'], 2, ',', '.'),
                    $period['count'],
                ], ';');
            }
            fputcsv($file, [], ';');

            fputcsv($file, ['ANALISIS POR TIPO DE MOVIMIENTO'], ';');
            fputcsv($file, ['Tipo', 'Ingresos', 'Egresos', 'Cantidad'], ';');
            foreach ($movementsByType as $type) {
                fputcsv($file, [
                    $type['type_name'],
                    number_format($type['inflows'], 2, ',', '.'),
                    number_format($type['outflows'], 2, ',', '.'),
                    $type['count'],
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Helper: convierte Expense externos a objetos compatibles con generateCashAnalysisData
     */
    private function buildExternalExpensesAsMovements($startDate, $endDate): \Illuminate\Support\Collection
    {
        return \App\Models\Expense::with('movementType')
            ->forDateRange($startDate, $endDate)
            ->get()
            ->map(function ($expense) {
                $obj = new \stdClass();
                $obj->created_at = \Carbon\Carbon::parse($expense->expense_date)->startOfDay();
                $obj->amount = -abs((float) $expense->amount);
                $obj->movementType = $expense->movementType;
                return $obj;
            });
    }

    /**
     * Helper: agrupa movimientos por día/semana/mes para Análisis de Caja
     */
    private function generateCashAnalysisData($movements, $groupBy, $startDate, $endDate)
    {
        $data = collect();

        switch ($groupBy) {
            case 'day':
                $period = $startDate->copy();
                while ($period->lte($endDate)) {
                    $dayMovements = $movements->filter(function ($movement) use ($period) {
                        return Carbon::parse($movement->created_at)->isSameDay($period);
                    });

                    $data->push([
                        'period' => $period->format('Y-m-d'),
                        'period_label' => $period->format('d/m/Y'),
                        'inflows' => $dayMovements->where('amount', '>', 0)->sum('amount'),
                        'outflows' => abs($dayMovements->where('amount', '<', 0)->sum('amount')),
                        'net' => $dayMovements->sum('amount'),
                        'count' => $dayMovements->count(),
                    ]);

                    $period->addDay();
                }
                break;

            case 'week':
                $period = $startDate->copy()->startOfWeek();
                while ($period->lte($endDate)) {
                    $weekEnd = $period->copy()->endOfWeek();
                    $weekMovements = $movements->filter(function ($movement) use ($period, $weekEnd) {
                        $moveDate = Carbon::parse($movement->created_at);
                        return $moveDate->between($period, $weekEnd);
                    });

                    $data->push([
                        'period' => $period->format('Y-m-d'),
                        'period_label' => 'Semana del ' . $period->format('d/m') . ' al ' . $weekEnd->format('d/m/Y'),
                        'inflows' => $weekMovements->where('amount', '>', 0)->sum('amount'),
                        'outflows' => abs($weekMovements->where('amount', '<', 0)->sum('amount')),
                        'net' => $weekMovements->sum('amount'),
                        'count' => $weekMovements->count(),
                    ]);

                    $period->addWeek();
                }
                break;

            case 'month':
                $period = $startDate->copy()->startOfMonth();
                while ($period->lte($endDate)) {
                    $monthEnd = $period->copy()->endOfMonth();
                    $monthMovements = $movements->filter(function ($movement) use ($period, $monthEnd) {
                        $moveDate = Carbon::parse($movement->created_at);
                        return $moveDate->between($period, $monthEnd);
                    });

                    $data->push([
                        'period' => $period->format('Y-m'),
                        'period_label' => $period->format('F Y'),
                        'inflows' => $monthMovements->where('amount', '>', 0)->sum('amount'),
                        'outflows' => abs($monthMovements->where('amount', '<', 0)->sum('amount')),
                        'net' => $monthMovements->sum('amount'),
                        'count' => $monthMovements->count(),
                    ]);

                    $period->addMonth();
                }
                break;
        }

        return $data->reverse();
    }
}
