<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\CashMovement;
use App\Models\Professional;
use Carbon\Carbon;
use Illuminate\Http\Request;

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
                $query->forDate($selectedDate);
            }])
            ->whereHas('appointments', function ($query) use ($selectedDate) {
                $query->forDate($selectedDate);
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
            return view('reports.daily-schedule-select', compact('allProfessionals', 'professionalsWithAppointments', 'selectedDate'));
        }

        // Obtener el profesional seleccionado
        $professional = Professional::with('specialty')->findOrFail($professionalId);

        // Obtener pacientes del día para el profesional
        $appointments = Appointment::with(['patient', 'paymentAppointments.payment'])
            ->where('professional_id', $professionalId)
            ->forDate($selectedDate)
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
            ->sortByDesc('is_urgency') // Urgencias primero
            ->values();

        // Estadísticas del día
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

        // Información adicional
        $reportData = [
            'professional' => $professional,
            'date' => $selectedDate,
            'appointments' => $appointments,
            'stats' => $stats,
            'generated_at' => now(),
            'generated_by' => auth()->user()->name ?? 'Sistema',
        ];

        // Si es para imprimir, devolver vista de impresión
        if ($request->get('print') === '1') {
            return view('reports.daily-schedule-print', compact('reportData'));
        }

        // Vista normal
        return view('reports.daily-schedule', compact('reportData', 'allProfessionals'));
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
            'generated_by' => auth()->user()->name ?? 'Sistema',
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
                ];
            })
            ->filter(function ($professional) {
                // Mostrar solo si tiene turnos atendidos Y si tiene monto pendiente diferente de $0
                // Si el monto es $0, significa que ya fue liquidado completamente
                return $professional['attended_count'] > 0 && $professional['professional_amount'] != 0;
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
            'generated_by' => auth()->user()->name ?? 'Sistema',
        ];

        // Si es para imprimir, devolver vista de impresión
        if ($request->get('print') === '1') {
            return view('reports.professional-liquidation-print', compact('liquidationData'));
        }

        // Vista normal
        return view('reports.professional-liquidation', compact('liquidationData', 'allProfessionals'));
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
}
