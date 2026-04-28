<?php

namespace App\Http\Controllers;

use App\Models\CashMovement;
use App\Models\MovementType;
use App\Models\Payment;
use App\Services\WhatsAppService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CashController extends Controller
{
    public function dailyCash(Request $request)
    {
        // Forzar siempre la fecha de hoy (no permitir ver días anteriores)
        $selectedDate = now();

        $previousDay = $selectedDate->copy()->subDay();
        $lastBalanceMovement = CashMovement::whereDate('created_at', '<=', $previousDay)
            ->orderBy('created_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->first();

        $initialBalance = $lastBalanceMovement ? $lastBalanceMovement->balance_after : 0;

        $query = CashMovement::with(['user', 'movementType', 'reference' => function($morphTo) {
                $morphTo->morphWith([
                    Payment::class => ['paymentDetails']
                ]);
            }])
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

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'summary' => $cashSummary,
                'movements' => $movements,
                'movements_by_type' => $movementsByType,
            ]);
        }

        return view('cash.daily', compact('cashSummary', 'movements', 'movementsByType'));
    }

    public function addExpense(Request $request)
    {
        if ($request->isMethod('get')) {
            // Obtener tipos de movimiento de GASTOS activos desde BD (categoría expense_detail)
            // Excluir tipos de sistema y retiros (withdrawal_detail)
            $excludedCodes = [
                'professional_payment',
                'cash_opening',
                'cash_closing',
                'patient_refund',
                // Gastos externos (no se cargan por caja)
                'salary',
                'tax',
                'external_rent',
                'external_services',
            ];

            $expenseTypes = \App\Models\MovementType::active()
                ->where('category', 'expense_detail') // Solo gastos, NO retiros
                ->whereNotIn('code', $excludedCodes)
                ->orderBy('order')
                ->get();

            // Convertir a array [code => name] para el select
            $expenseCategories = $expenseTypes->pluck('name', 'code')->toArray();

            return view('cash.expense-form', compact('expenseCategories'));
        }

        // Validación dinámica: obtener códigos válidos desde BD (solo categoría expense_detail)
        $validCodes = \App\Models\MovementType::active()
            ->where('category', 'expense_detail') // Solo gastos, NO retiros
            ->whereNotIn('code', ['professional_payment', 'cash_opening', 'cash_closing', 'patient_refund', 'salary', 'tax', 'external_rent', 'external_services'])
            ->pluck('code')
            ->toArray();

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string|in:cash,transfer,debit_card,credit_card,qr',
            'description' => 'required|string|max:500',
            'category' => 'required|string|in:' . implode(',', $validCodes),
            'receipt_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            // Verificar que la caja esté abierta
            if (! CashMovement::isCashOpenToday()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pueden registrar gastos. La caja debe estar abierta para realizar esta operación.',
                ], 400);
            }

            $receiptPath = null;
            if ($request->hasFile('receipt_file')) {
                $receiptPath = $request->file('receipt_file')->store('cash/receipts', 'public');
            }

            // Obtener balance actual con lock pesimista
            $currentBalance = CashMovement::getCurrentBalanceWithLock();
            $newBalance = $currentBalance - $validated['amount'];

            $description = $validated['description'];

            if ($validated['notes']) {
                $description .= ' - '.$validated['notes'];
            }

            // Crear registro en tabla payments (genera receipt_number automáticamente)
            $payment = \App\Models\Payment::create([
                'patient_id' => null, // Gastos no tienen paciente
                'payment_date' => now(),
                'payment_type' => 'manual_income', // Egresos: manual_income con total_amount negativo
                'total_amount' => -$validated['amount'], // Negativo para egreso
                'is_advance_payment' => false,
                'concept' => $description,
                'status' => 'confirmed',
                'liquidation_status' => 'not_applicable',
                'income_category' => $validated['category'],
                'created_by' => auth()->id(),
            ]);

            // Crear payment_detail con el método de pago
            \App\Models\PaymentDetail::create([
                'payment_id' => $payment->id,
                'payment_method' => $validated['payment_method'],
                'amount' => $validated['amount'],
                'received_by' => 'centro',
                'reference' => null,
            ]);

            // Usar el tipo específico de gasto (subcategoría) en lugar del genérico 'expense'
            $movementTypeCode = $validated['category']; // ej: 'office_supplies', 'medical_supplies', etc.

            $cashMovement = CashMovement::create([
                'movement_type_id' => MovementType::getIdByCode($movementTypeCode),
                'amount' => -$validated['amount'], // Negativo para egreso
                'description' => $description,
                'reference_type' => 'App\\Models\\Payment', // Vincular al Payment
                'reference_id' => $payment->id,
                'balance_after' => $newBalance,
                'user_id' => auth()->id(),
            ]);

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Gasto registrado exitosamente.',
                    'payment_id' => $payment->id,
                    'receipt_number' => $payment->receipt_number,
                    'cash_movement_id' => $cashMovement->id,
                    'new_balance' => $newBalance,
                ]);
            }

            return redirect()->route('cash.daily')
                ->with('success', 'Gasto registrado exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al registrar el gasto: '.$e->getMessage(),
                ], 500);
            }

            return redirect()->back()
                ->withErrors(['error' => 'Error al registrar el gasto: '.$e->getMessage()])
                ->withInput();
        }
    }

    public function getCashMovementDetails(CashMovement $cashMovement)
    {
        $cashMovement->load(['user', 'movementType']);

        $additionalData = [];

        try {
            // Cargar información adicional según el tipo de movimiento y reference_type
            if ($cashMovement->movementType?->code === 'patient_payment' && $cashMovement->reference_id &&
                in_array($cashMovement->reference_type, ['payment', 'App\\Models\\Payment'])) {
                $payment = \App\Models\Payment::with(['patient', 'paymentAppointments.appointment.professional'])
                    ->find($cashMovement->reference_id);
                if ($payment) {
                    $additionalData['payment'] = $payment;
                }
            } elseif ($cashMovement->reference_id &&
                     in_array($cashMovement->reference_type, ['professional', 'App\\Models\\Professional'])) {
                // Carga profesional para liquidaciones y reintegros
                $professional = \App\Models\Professional::with(['specialty'])->find($cashMovement->reference_id);
                if ($professional) {
                    if ($cashMovement->movementType?->code === 'professional_payment') {
                        $additionalData['professional'] = $professional;
                    } elseif ($cashMovement->movementType?->code === 'expense') {
                        // Reintegro a paciente
                        $additionalData['refund_professional'] = $professional;
                    }
                }
            }
        } catch (\Exception $e) {
            // Si hay error cargando relaciones adicionales, continuar sin ellas
            \Log::warning('Error loading additional movement details: '.$e->getMessage());
        }

        return response()->json([
            'success' => true,
            'cash_movement' => $cashMovement,
            'additional_data' => $additionalData,
        ]);
    }

    public function openCash(Request $request)
    {
        $validated = $request->validate([
            'opening_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            $today = now()->startOfDay();

            // Verificar que no haya cajas sin cerrar de días anteriores
            $unclosedDate = CashMovement::hasUnclosedCash();
            if ($unclosedDate) {
                return response()->json([
                    'success' => false,
                    'message' => "No se puede abrir la caja de hoy. Primero debe cerrar la caja del día {$unclosedDate}.",
                    'unclosed_date' => $unclosedDate,
                ], 400);
            }

            // Verificar que no exista ya una apertura para hoy
            $existingOpening = CashMovement::forDate($today)->openingMovements()->first();
            if ($existingOpening) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ya existe una apertura de caja para el día de hoy.',
                ], 400);
            }

            // Obtener saldo anterior con lock pesimista
            $previousDay = $today->copy()->subDay();
            $lastMovement = CashMovement::whereDate('created_at', '<=', $previousDay)
                ->orderBy('created_at', 'desc')
                ->orderBy('created_at', 'desc')
                ->lockForUpdate()
                ->first();

            $previousBalance = $lastMovement ? $lastMovement->balance_after : 0;
            $openingAmount = $validated['opening_amount'] ?? 0;
            $newBalance = $previousBalance + $openingAmount;

            // Crear movimiento de apertura
            $cashMovement = CashMovement::create([
                                'movement_type_id' => MovementType::getIdByCode('cash_opening'),
                'amount' => $openingAmount,
                'description' => 'Apertura de caja - '.($validated['notes'] ?: 'Apertura del día'),
                'reference_type' => null,
                'reference_id' => null,
                'balance_after' => $newBalance,
                'user_id' => auth()->id(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Caja abierta exitosamente.',
                'cash_movement' => $cashMovement,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error al abrir la caja: '.$e->getMessage(),
            ], 500);
        }
    }

    public function closeCash(Request $request)
    {
        $validated = $request->validate([
            'closing_amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500',
            'close_date' => 'nullable|date',
        ]);

        try {
            DB::beginTransaction();

            // Buscar la última apertura sin cierre correspondiente (independiente de la fecha)
            $openingTypeId = MovementType::getIdByCode('cash_opening');
            $closingTypeId = MovementType::getIdByCode('cash_closing');

            $opening = CashMovement::where('movement_type_id', $openingTypeId)
                ->whereNotExists(function ($query) use ($closingTypeId) {
                    $query->select('id')
                        ->from('cash_movements as cm2')
                        ->whereRaw('DATE(cm2.created_at) = DATE(cash_movements.created_at)')
                        ->where('cm2.movement_type_id', $closingTypeId);
                })
                ->orderBy('created_at', 'desc')
                ->first();

            if (! $opening) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró una caja abierta para cerrar.',
                ], 400);
            }

            // La fecha del cierre debe ser la del día de apertura
            $openingDate = $opening->created_at->format('Y-m-d');
            $closeDate = Carbon::parse($openingDate);

            // Verificar que todos los profesionales con turnos atendidos del día hayan sido liquidados
            // EXCLUIR profesionales cuyo monto total de turnos sea $0
            $professionalsWithAppointments = \App\Models\Professional::whereHas('appointments', function ($query) use ($closeDate) {
                $query->whereDate('appointment_date', $closeDate)
                      ->where('status', 'attended');
            })->get();

            $professionalsNotLiquidated = $professionalsWithAppointments->filter(function ($professional) use ($closeDate) {
                // Calcular monto total de turnos atendidos del día
                $totalAmount = \App\Models\Appointment::where('professional_id', $professional->id)
                    ->whereDate('appointment_date', $closeDate)
                    ->where('status', 'attended')
                    ->sum('final_amount');

                // Si el monto total es $0, no requiere liquidación
                if ($totalAmount == 0) {
                    return false;
                }

                // Verificar si hay payment_details pendientes de liquidar
                $hasPendingPayments = \App\Models\PaymentDetail::whereHas('payment.paymentAppointments.appointment', function ($query) use ($professional, $closeDate) {
                        $query->where('professional_id', $professional->id)
                              ->whereDate('appointment_date', $closeDate)
                              ->where('status', 'attended');
                    })
                    ->whereNull('liquidation_id')
                    ->exists();

                return $hasPendingPayments; // Retorna true si HAY payment_details pendientes
            });

            if ($professionalsNotLiquidated->isNotEmpty()) {
                $professionalNames = $professionalsNotLiquidated->map(function ($professional) {
                    return "Dr. {$professional->first_name} {$professional->last_name}";
                })->join(', ');

                return response()->json([
                    'success' => false,
                    'message' => 'No se puede cerrar la caja. Los siguientes profesionales tienen turnos atendidos sin liquidar: ' . $professionalNames . '. Por favor, liquide a todos los profesionales antes de cerrar la caja.',
                    'professionals_not_liquidated' => $professionalsNotLiquidated->values(),
                ], 400);
            }

            // Obtener el saldo actual antes del cierre con lock pesimista
            $lastMovement = CashMovement::whereDate('created_at', '<=', $closeDate)
                ->orderBy('id', 'desc')
                ->orderBy('created_at', 'desc')
                ->lockForUpdate()
                ->first();

            $currentBalance = $lastMovement ? $lastMovement->balance_after : 0;

            // Calcular la fecha/hora del cierre: mismo día de apertura a las 23:59:59
            $closingDateTime = Carbon::parse($openingDate)->setTime(23, 59, 59);

            // Crear movimiento de cierre que retira todo el saldo
            $cashMovement = new CashMovement([
                'movement_type_id' => MovementType::getIdByCode('cash_closing'),
                'amount' => -$currentBalance, // Retirar todo el saldo actual
                'description' => $this->buildClosingDescription($validated, $closingDateTime, $currentBalance),
                'reference_type' => null,
                'reference_id' => null,
                'balance_after' => 0, // Balance queda en cero
                'user_id' => auth()->id(),
            ]);

            // Deshabilitar timestamps automáticos para poder forzar created_at
            $cashMovement->timestamps = false;
            $cashMovement->created_at = $closingDateTime;
            $cashMovement->updated_at = now(); // Mantener la hora real de actualización para auditoría
            $cashMovement->save();

            $difference = $validated['closing_amount'] - $currentBalance;

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Caja cerrada exitosamente.',
                'cash_movement' => $cashMovement,
                'redirect_url' => route('cash.daily-report', ['date' => $closeDate->format('Y-m-d')]),
                'summary' => [
                    'theoretical_balance' => $currentBalance,
                    'counted_amount' => $validated['closing_amount'],
                    'difference' => $difference,
                    'date' => $closeDate->format('d/m/Y'),
                    'withdrawn_amount' => $currentBalance,
                ],
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error al cerrar la caja: '.$e->getMessage(),
            ], 500);
        }
    }

    public function cashCount(Request $request)
    {
        // Arqueo de caja: genera reporte sin cerrar la caja
        $selectedDate = now()->startOfDay();

        // Obtener el saldo inicial del día anterior
        $previousDay = $selectedDate->copy()->subDay();
        $lastBalanceMovement = CashMovement::whereDate('created_at', '<=', $previousDay)
            ->orderBy('created_at', 'desc')
            ->first();

        $initialBalance = $lastBalanceMovement ? $lastBalanceMovement->balance_after : 0;

        // Obtener todos los movimientos del día
        $movements = CashMovement::with(['user', 'movementType'])
            ->with(['reference' => function($morphTo) {
                $morphTo->morphWith([
                    Payment::class => ['paymentDetails']
                ]);
            }])
            ->whereDate('created_at', $selectedDate)
            ->orderBy('created_at')
            ->get();

        // Calcular totales (excluyendo apertura y cierre de caja)
        $movementsForTotals = $movements->filter(function($movement) {
            return !in_array($movement->movementType?->code, ['cash_opening', 'cash_closing']);
        });
        $inflows = $movementsForTotals->where('amount', '>', 0)->sum('amount');
        $outflows = $movementsForTotals->where('amount', '<', 0)->sum('amount');
        $finalBalance = $initialBalance + $inflows + $outflows;

        // Obtener movimientos de apertura
        $openingMovement = $movements->first(function($movement) {
            return $movement->movementType?->code === 'cash_opening';
        });

        // Resumen general
        $summary = [
            'date' => $selectedDate,
            'initial_balance' => $initialBalance,
            'total_inflows' => $inflows,
            'total_outflows' => abs($outflows),
            'final_balance' => $finalBalance,
            'opening_movement' => $openingMovement,
            'closing_movement' => null, // No hay cierre en arqueo
            'counted_amount' => 0,
            'difference' => 0,
            'is_closed' => false,
        ];

        // Agrupar por tipo de movimiento (excluyendo apertura y cierre)
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
                    'icon' => $firstMovement->movementType?->icon ?? '',
                    'inflows' => $group->where('amount', '>', 0)->sum('amount'),
                    'outflows' => abs($group->where('amount', '<', 0)->sum('amount')),
                    'count' => $group->count(),
                ];
            });

        // Resumen por usuario - simplificado
        $userSummary = collect();

        // NUEVO v2.6.0: Consulta directa por método de pago
        $paymentsByProfessionalAndMethod = DB::select("
            SELECT
                pr.id AS professional_id,
                CONCAT(pr.last_name, ', ', pr.first_name) AS profesional,
                s.name AS specialty,
                pr.commission_percentage,
                pd.payment_method,
                SUM(pd.amount) AS total_amount,
                COUNT(DISTINCT pa.appointment_id) AS appointment_count
            FROM payment_details pd
            JOIN payments pay ON pay.id = pd.payment_id
            JOIN payment_appointments pa ON pa.payment_id = pay.id
            JOIN appointments a ON a.id = pa.appointment_id
            JOIN professionals pr ON pr.id = pa.professional_id
            LEFT JOIN specialties s ON s.id = pr.specialty_id
            WHERE pay.status = 'confirmed'
              AND DATE(pay.payment_date) = ?
            GROUP BY pr.id, pr.last_name, pr.first_name, s.name, pr.commission_percentage, pd.payment_method
            ORDER BY profesional, pd.payment_method
        ", [$selectedDate->format('Y-m-d')]);

        // Determinar qué métodos de pago tienen datos (para columnas dinámicas)
        $activePaymentMethods = collect($paymentsByProfessionalAndMethod)
            ->pluck('payment_method')
            ->unique()
            ->sort()
            ->values();

        // Transformar resultados agrupando por profesional
        $professionalIncome = collect($paymentsByProfessionalAndMethod)
            ->groupBy('professional_id')
            ->map(function($group) use ($activePaymentMethods) {
                $first = $group->first();

                // Crear array con todos los métodos en 0
                $paymentMethods = [];
                foreach(['cash', 'transfer', 'debit_card', 'credit_card', 'qr'] as $method) {
                    $paymentMethods[$method] = 0;
                }

                // Sumar montos por método
                $totalCollected = 0;
                $totalAppointments = 0;
                foreach($group as $row) {
                    $paymentMethods[$row->payment_method] = $row->total_amount;
                    $totalCollected += $row->total_amount;
                    $totalAppointments += $row->appointment_count;
                }

                // Calcular comisiones
                $commissionPercentage = $first->commission_percentage ?? 0;
                $professionalAmount = $totalCollected * ($commissionPercentage / 100);
                $clinicAmount = $totalCollected - $professionalAmount;

                return [
                    'professional_id' => $first->professional_id,
                    'full_name' => "Dr. " . $first->profesional,
                    'specialty' => $first->specialty ?? 'N/A',
                    'commission_percentage' => $commissionPercentage,
                    'total_collected' => $totalCollected,
                    'professional_amount' => $professionalAmount,
                    'clinic_amount' => $clinicAmount,
                    'count' => $totalAppointments,
                    'cash' => $paymentMethods['cash'],
                    'transfer' => $paymentMethods['transfer'],
                    'debit_card' => $paymentMethods['debit_card'],
                    'credit_card' => $paymentMethods['credit_card'],
                    'qr' => $paymentMethods['qr'],
                ];
            })
            ->values();

        // Calcular liquidación de Dra. Zalazar (professional_id = 1)
        $zalazarData = $professionalIncome->firstWhere('professional_id', 1);
        $zalazarCommission = $zalazarData ? $zalazarData['professional_amount'] : 0;

        // Obtener movimientos de "Pago de Saldos Dra. Zalazar"
        $zalazarBalancePayments = $movements->filter(fn($m) => $m->movementType?->code === 'zalazar_balance_payment');
        $zalazarBalanceTotal = $zalazarBalancePayments->sum('amount');

        // Total de ingresos de Dra. Zalazar
        $zalazarTotalIncome = $zalazarCommission + $zalazarBalanceTotal;

        // Calcular desglose por método de pago de Dra. Zalazar
        $zalazarPaymentBreakdown = collect([
            'cash' => 0,
            'transfer' => 0,
            'debit_card' => 0,
            'credit_card' => 0,
            'qr' => 0,
        ]);

        // Sumar liquidación de pacientes (desde $zalazarData)
        if ($zalazarData) {
            $commissionPercentage = $zalazarData['commission_percentage'] / 100;
            foreach(['cash', 'transfer', 'debit_card', 'credit_card', 'qr'] as $method) {
                $zalazarPaymentBreakdown[$method] = ($zalazarData[$method] ?? 0) * $commissionPercentage;
            }
        }

        // Sumar pagos de saldos (desde payment_details del pago referenciado en cash_movements)
        foreach ($zalazarBalancePayments as $movement) {
            // Si el movimiento tiene reference que es un Payment, usar sus payment_details
            if ($movement->reference instanceof Payment) {
                foreach ($movement->reference->paymentDetails as $detail) {
                    if (isset($zalazarPaymentBreakdown[$detail->payment_method])) {
                        $zalazarPaymentBreakdown[$detail->payment_method] += $detail->amount;
                    }
                }
            }
        }

        // Agregar al summary
        $summary['zalazar_liquidation'] = $zalazarCommission;
        $summary['zalazar_balance_payments'] = $zalazarBalanceTotal;
        $summary['zalazar_total_income'] = $zalazarTotalIncome;
        $summary['final_balance_with_zalazar'] = $finalBalance + $zalazarCommission;
        $summary['zalazar_payment_breakdown'] = $zalazarPaymentBreakdown;

        return view('cash.count-report', compact(
            'selectedDate',
            'summary',
            'movements',
            'movementsByType',
            'userSummary',
            'professionalIncome',
            'zalazarBalancePayments',
            'activePaymentMethods'
        ));
    }

    public function getCashStatus(Request $request)
    {
        $date = $request->get('date', now()->format('Y-m-d'));
        $selectedDate = Carbon::parse($date);

        $status = CashMovement::getCashStatusForDate($selectedDate);
        $unclosedDate = CashMovement::hasUnclosedCash();

        return response()->json([
            'success' => true,
            'status' => $status,
            'unclosed_date' => $unclosedDate,
            'date' => $selectedDate->format('Y-m-d'),
        ]);
    }

    public function dailyReport(Request $request)
    {
        $date = $request->get('date', now()->format('Y-m-d'));
        $selectedDate = Carbon::parse($date);

        // Obtener el saldo inicial del día anterior
        $previousDay = $selectedDate->copy()->subDay();
        $lastBalanceMovement = CashMovement::whereDate('created_at', '<=', $previousDay)
            ->orderBy('created_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->first();

        $initialBalance = $lastBalanceMovement ? $lastBalanceMovement->balance_after : 0;

        // Obtener todos los movimientos del día
        $movements = CashMovement::with(['user', 'movementType'])
            ->with(['reference' => function($morphTo) {
                $morphTo->morphWith([
                    Payment::class => ['paymentDetails']
                ]);
            }])
            ->whereDate('created_at', $selectedDate)
            ->orderBy('created_at')
            ->get();

        // Calcular totales (excluyendo apertura y cierre de caja)
        $movementsForTotals = $movements->filter(function($movement) {
            return !in_array($movement->movementType?->code, ['cash_opening', 'cash_closing']);
        });
        $inflows = $movementsForTotals->where('amount', '>', 0)->sum('amount');
        $outflows = $movementsForTotals->where('amount', '<', 0)->sum('amount');
        $finalBalance = $initialBalance + $inflows + $outflows;

        // Obtener movimientos de apertura y cierre
        $openingMovement = $movements->first(function($movement) {
            return $movement->movementType?->code === 'cash_opening';
        });
        $closingMovement = $movements->first(function($movement) {
            return $movement->movementType?->code === 'cash_closing';
        });

        // Calcular efectivo contado y diferencia si hay cierre
        $countedAmount = 0;
        $difference = 0;

        if ($closingMovement) {
            // Extraer el monto contado de la descripción del cierre
            preg_match('/\$([0-9,]+\.?\d*)/', $closingMovement->description, $matches);
            if (isset($matches[1])) {
                $countedAmount = floatval(str_replace(',', '', $matches[1]));
                $difference = $countedAmount - $finalBalance;
            }
        }

        // Resumen general
        $summary = [
            'date' => $selectedDate,
            'initial_balance' => $initialBalance,
            'total_inflows' => $inflows,
            'total_outflows' => abs($outflows),
            'final_balance' => $finalBalance,
            'opening_movement' => $openingMovement,
            'closing_movement' => $closingMovement,
            'counted_amount' => $countedAmount,
            'difference' => $difference,
            'is_closed' => $closingMovement !== null,
        ];

        // Agrupar por tipo de movimiento (excluyendo apertura y cierre)
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
                    'icon' => $firstMovement->movementType?->icon ?? '',
                    'inflows' => $group->where('amount', '>', 0)->sum('amount'),
                    'outflows' => abs($group->where('amount', '<', 0)->sum('amount')),
                    'count' => $group->count(),
                ];
            });

        // Resumen por usuario - simplificado para debug
        $userSummary = collect(); // Temporalmente vacío para evitar errores

        // NUEVO v2.6.0: Consulta directa por método de pago
        $paymentsByProfessionalAndMethod = DB::select("
            SELECT
                pr.id AS professional_id,
                CONCAT(pr.last_name, ', ', pr.first_name) AS profesional,
                s.name AS specialty,
                pr.commission_percentage,
                pd.payment_method,
                SUM(pd.amount) AS total_amount,
                COUNT(DISTINCT pa.appointment_id) AS appointment_count
            FROM payment_details pd
            JOIN payments pay ON pay.id = pd.payment_id
            JOIN payment_appointments pa ON pa.payment_id = pay.id
            JOIN appointments a ON a.id = pa.appointment_id
            JOIN professionals pr ON pr.id = pa.professional_id
            LEFT JOIN specialties s ON s.id = pr.specialty_id
            WHERE pay.status = 'confirmed'
              AND DATE(pay.payment_date) = ?
            GROUP BY pr.id, pr.last_name, pr.first_name, s.name, pr.commission_percentage, pd.payment_method
            ORDER BY profesional, pd.payment_method
        ", [$selectedDate->format('Y-m-d')]);

        // Determinar qué métodos de pago tienen datos (para columnas dinámicas)
        $activePaymentMethods = collect($paymentsByProfessionalAndMethod)
            ->pluck('payment_method')
            ->unique()
            ->sort()
            ->values();

        // Transformar resultados agrupando por profesional
        $professionalIncome = collect($paymentsByProfessionalAndMethod)
            ->groupBy('professional_id')
            ->map(function($group) use ($activePaymentMethods) {
                $first = $group->first();

                // Crear array con todos los métodos en 0
                $paymentMethods = [];
                foreach(['cash', 'transfer', 'debit_card', 'credit_card', 'qr'] as $method) {
                    $paymentMethods[$method] = 0;
                }

                // Sumar montos por método
                $totalCollected = 0;
                $totalAppointments = 0;
                foreach($group as $row) {
                    $paymentMethods[$row->payment_method] = $row->total_amount;
                    $totalCollected += $row->total_amount;
                    $totalAppointments += $row->appointment_count;
                }

                // Calcular comisiones
                $commissionPercentage = $first->commission_percentage ?? 0;
                $professionalAmount = $totalCollected * ($commissionPercentage / 100);
                $clinicAmount = $totalCollected - $professionalAmount;

                return [
                    'professional_id' => $first->professional_id,
                    'full_name' => "Dr. " . $first->profesional,
                    'specialty' => $first->specialty ?? 'N/A',
                    'commission_percentage' => $commissionPercentage,
                    'total_collected' => $totalCollected,
                    'professional_amount' => $professionalAmount,
                    'clinic_amount' => $clinicAmount,
                    'count' => $totalAppointments,
                    'cash' => $paymentMethods['cash'],
                    'transfer' => $paymentMethods['transfer'],
                    'debit_card' => $paymentMethods['debit_card'],
                    'credit_card' => $paymentMethods['credit_card'],
                    'qr' => $paymentMethods['qr'],
                ];
            })
            ->values();

        // Calcular liquidación de Dra. Zalazar (professional_id = 1) para saldo final
        $zalazarData = $professionalIncome->firstWhere('professional_id', 1);
        $zalazarCommission = $zalazarData ? $zalazarData['professional_amount'] : 0;

        // Obtener movimientos de "Pago de Saldos Dra. Zalazar"
        $zalazarBalancePayments = $movements->filter(fn($m) => $m->movementType?->code === 'zalazar_balance_payment');
        $zalazarBalanceTotal = $zalazarBalancePayments->sum('amount');

        // Total de ingresos de Dra. Zalazar (liquidación + pagos de saldos)
        // NOTA: Los pagos de saldos ya están incluidos en $finalBalance (son ingresos del día)
        $zalazarTotalIncome = $zalazarCommission + $zalazarBalanceTotal;

        // Calcular desglose por método de pago de Dra. Zalazar
        $zalazarPaymentBreakdown = collect([
            'cash' => 0,
            'transfer' => 0,
            'debit_card' => 0,
            'credit_card' => 0,
            'qr' => 0,
        ]);

        // Sumar liquidación de pacientes (desde $zalazarData)
        if ($zalazarData) {
            $commissionPercentage = $zalazarData['commission_percentage'] / 100;
            foreach(['cash', 'transfer', 'debit_card', 'credit_card', 'qr'] as $method) {
                $zalazarPaymentBreakdown[$method] = ($zalazarData[$method] ?? 0) * $commissionPercentage;
            }
        }

        // Sumar pagos de saldos (desde payment_details del pago referenciado en cash_movements)
        foreach ($zalazarBalancePayments as $movement) {
            // Si el movimiento tiene reference que es un Payment, usar sus payment_details
            if ($movement->reference instanceof Payment) {
                foreach ($movement->reference->paymentDetails as $detail) {
                    if (isset($zalazarPaymentBreakdown[$detail->payment_method])) {
                        $zalazarPaymentBreakdown[$detail->payment_method] += $detail->amount;
                    }
                }
            }
        }

        // Agregar al summary el saldo final que incluye SOLO la liquidación de Zalazar
        // Los pagos de saldos ya están incluidos en finalBalance, por eso no se suman de nuevo
        $summary['zalazar_liquidation'] = $zalazarCommission;
        $summary['zalazar_balance_payments'] = $zalazarBalanceTotal;
        $summary['zalazar_total_income'] = $zalazarTotalIncome;
        $summary['final_balance_with_zalazar'] = $finalBalance + $zalazarCommission;
        $summary['zalazar_payment_breakdown'] = $zalazarPaymentBreakdown;

        return view('cash.daily-report', compact(
            'selectedDate',
            'summary',
            'movements',
            'movementsByType',
            'userSummary',
            'professionalIncome',
            'zalazarBalancePayments',
            'activePaymentMethods'
        ));
    }

    public function withdrawalForm(Request $request)
    {
        if ($request->isMethod('get')) {
            // Obtener tipos de RETIROS activos desde BD (categoría withdrawal_detail)
            // Excluir tipos de sistema y gastos (expense_detail)
            $withdrawalTypes = \App\Models\MovementType::active()
                ->where('category', 'withdrawal_detail') // Solo retiros, NO gastos
                ->orderBy('order')
                ->get();

            // Convertir a array [code => name] para el select
            $withdrawalTypes = $withdrawalTypes->pluck('name', 'code')->toArray();

            return view('cash.withdrawal-form', compact('withdrawalTypes'));
        }

        // Validación dinámica: obtener códigos válidos desde BD (solo categoría withdrawal_detail)
        $validCodes = \App\Models\MovementType::active()
            ->where('category', 'withdrawal_detail') // Solo retiros, NO gastos
            ->pluck('code')
            ->toArray();

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string|in:cash,transfer,debit_card,credit_card,qr',
            'withdrawal_type' => 'required|string|in:' . implode(',', $validCodes),
            'description' => 'required|string|max:500',
            'recipient' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            // Verificar que la caja esté abierta
            if (! CashMovement::isCashOpenToday()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pueden realizar retiros. La caja debe estar abierta para realizar esta operación.',
                ], 400);
            }

            // Verificar que hay suficiente efectivo en caja (con lock pesimista)
            $currentBalance = CashMovement::getCurrentBalanceWithLock();

            if ($currentBalance < $validated['amount']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Saldo insuficiente en caja. Disponible: $'.number_format($currentBalance, 2),
                ], 400);
            }

            $newBalance = $currentBalance - $validated['amount'];

            $description = 'Retiro: '.$validated['description'];
            if ($validated['recipient']) {
                $description .= ' - Destinatario: '.$validated['recipient'];
            }
            if ($validated['notes']) {
                $description .= ' - '.$validated['notes'];
            }

            // Crear registro en tabla payments (genera receipt_number automáticamente)
            $payment = \App\Models\Payment::create([
                'patient_id' => null, // Retiros no tienen paciente
                'payment_date' => now(),
                'payment_type' => 'withdrawal',
                'total_amount' => -$validated['amount'], // Negativo para retiro
                'is_advance_payment' => false,
                'concept' => $description,
                'status' => 'confirmed',
                'liquidation_status' => 'not_applicable',
                'income_category' => $validated['withdrawal_type'],
                'created_by' => auth()->id(),
            ]);

            // Crear payment_detail con el método de pago
            \App\Models\PaymentDetail::create([
                'payment_id' => $payment->id,
                'payment_method' => $validated['payment_method'],
                'amount' => $validated['amount'],
                'received_by' => 'centro',
                'reference' => null,
            ]);

            // Usar el tipo específico de retiro (subcategoría) en lugar del genérico 'cash_withdrawal'
            $movementTypeCode = $validated['withdrawal_type']; // ej: 'bank_deposit', 'safe_custody', etc.

            $cashMovement = CashMovement::create([
                'movement_type_id' => MovementType::getIdByCode($movementTypeCode),
                'amount' => -$validated['amount'], // Negativo para salida
                'description' => $description,
                'reference_type' => 'App\\Models\\Payment', // Vincular al Payment
                'reference_id' => $payment->id,
                'balance_after' => $newBalance,
                'user_id' => auth()->id(),
            ]);

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Retiro registrado exitosamente.',
                    'payment_id' => $payment->id,
                    'receipt_number' => $payment->receipt_number,
                    'cash_movement_id' => $cashMovement->id,
                    'new_balance' => $newBalance,
                ]);
            }

            return redirect()->route('cash.daily')
                ->with('success', 'Retiro registrado exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al registrar el retiro: '.$e->getMessage(),
                ], 500);
            }

            return redirect()->back()
                ->withErrors(['error' => 'Error al registrar el retiro: '.$e->getMessage()])
                ->withInput();
        }
    }

    public function manualIncomeForm(Request $request)
    {
        if ($request->isMethod('get')) {
            // Obtener tipos de movimiento de INGRESOS activos desde BD (categoría income_detail)
            // Excluir tipos de sistema: patient_payment, cash_opening, cash_closing
            $excludedCodes = ['patient_payment', 'cash_opening', 'cash_closing'];

            $incomeTypes = \App\Models\MovementType::active()
                ->where('category', 'income_detail') // Solo ingresos manuales
                ->whereNotIn('code', $excludedCodes)
                ->orderBy('order')
                ->get();

            // Convertir a array [code => name] para el select
            $incomeCategories = $incomeTypes->pluck('name', 'code')->toArray();

            // Obtener TODOS los profesionales activos
            $professionals = \App\Models\Professional::active()
                ->with('specialty')
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->get();

            return view('cash.manual-income-form', compact('incomeCategories', 'professionals'));
        }

        // Validación dinámica: obtener códigos válidos desde BD (solo categoría income_detail)
        $validCodes = \App\Models\MovementType::active()
            ->where('category', 'income_detail') // Solo ingresos manuales
            ->whereNotIn('code', ['patient_payment', 'cash_opening', 'cash_closing'])
            ->pluck('code')
            ->toArray();

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'category' => 'required|string|in:' . implode(',', $validCodes),
            'payment_method' => 'required|string|in:cash,transfer,debit_card,credit_card,qr',
            'description' => 'required|string|max:500',
            'professional_id' => 'nullable|exists:professionals,id|required_if:category,professional_module_payment',
            'receipt_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            // Verificar que la caja esté abierta
            if (! CashMovement::isCashOpenToday()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pueden registrar ingresos. La caja debe estar abierta para realizar esta operación.',
                ], 400);
            }

            $receiptPath = null;
            if ($request->hasFile('receipt_file')) {
                $receiptPath = $request->file('receipt_file')->store('receipts', 'public');
            }

            // Obtener balance actual con lock pesimista
            $currentBalance = CashMovement::getCurrentBalanceWithLock();
            $newBalance = $currentBalance + $validated['amount'];

            // Obtener nombre del tipo de movimiento desde BD
            $movementType = \App\Models\MovementType::where('code', $validated['category'])->first();
            $categoryLabel = $movementType ? $movementType->name : ucfirst(str_replace('_', ' ', $validated['category']));

            $description = '['.$categoryLabel.'] '.$validated['description'];

            // Si es pago módulo profesional, agregar nombre del profesional a la descripción
            if ($validated['category'] === 'professional_module_payment' && isset($validated['professional_id'])) {
                $professional = \App\Models\Professional::find($validated['professional_id']);
                if ($professional) {
                    $description = "Pago Módulo - Dr. {$professional->first_name} {$professional->last_name} - " . $validated['description'];
                }
            }

            if ($validated['notes']) {
                $description .= ' - '.$validated['notes'];
            }

            // Crear registro en tabla payments (genera receipt_number automáticamente)
            $payment = \App\Models\Payment::create([
                'patient_id' => null, // Ingresos manuales no tienen paciente
                'payment_date' => now(),
                'payment_type' => 'manual_income',
                'total_amount' => $validated['amount'],
                'is_advance_payment' => false,
                'concept' => $description,
                'status' => 'confirmed',
                'liquidation_status' => 'not_applicable', // Los ingresos manuales no se liquidan
                'income_category' => $validated['category'],
                'created_by' => auth()->id(),
            ]);

            // Crear payment_detail con el método de pago
            // Nota: Los ingresos manuales siempre van al centro porque no están vinculados a turnos ni profesionales
            \App\Models\PaymentDetail::create([
                'payment_id' => $payment->id,
                'payment_method' => $validated['payment_method'],
                'amount' => $validated['amount'],
                'received_by' => 'centro',
                'reference' => $validated['reference'] ?? null,
            ]);

            // Crear registro en cash_movements vinculado al payment
            $movementTypeCode = $validated['category']; // ej: 'professional_module_payment', 'correction', etc.

            $cashMovement = CashMovement::create([
                'movement_type_id' => MovementType::getIdByCode($movementTypeCode),
                'amount' => $validated['amount'],
                'description' => $description,
                'reference_type' => 'App\\Models\\Payment', // Vincular al Payment
                'reference_id' => $payment->id,
                'balance_after' => $newBalance,
                'user_id' => auth()->id(),
            ]);

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Ingreso manual registrado exitosamente.',
                    'payment_id' => $payment->id, // Retornar payment_id en lugar de cash_movement_id
                    'receipt_number' => $payment->receipt_number,
                    'cash_movement_id' => $cashMovement->id,
                    'new_balance' => $newBalance,
                ]);
            }

            return redirect()->route('cash.daily')
                ->with('success', 'Ingreso manual registrado exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al registrar el ingreso: '.$e->getMessage(),
                ], 500);
            }

            return redirect()->back()
                ->withErrors(['error' => 'Error al registrar el ingreso: '.$e->getMessage()])
                ->withInput();
        }
    }

    public function printIncomeReceipt(Request $request, $paymentId)
    {
        $payment = \App\Models\Payment::with(['createdBy'])
            ->findOrFail($paymentId);

        // Verificar que sea un ingreso manual
        if ($payment->payment_type !== 'manual_income') {
            abort(403, 'Este no es un ingreso manual.');
        }

        return view('receipts.income-print', compact('payment'));
    }

    /**
     * POST /cash/income-receipt/{payment}/share-whatsapp
     */
    public function shareIncomeReceiptViaWhatsApp(Request $request, Payment $payment, WhatsAppService $whatsApp): JsonResponse
    {
        if ($payment->payment_type !== 'manual_income') {
            return response()->json([
                'success' => false,
                'message' => 'Este pago no corresponde a un ingreso manual.',
            ], 422);
        }

        // Ingresos manuales no tienen paciente asociado.
        if (empty($payment->patient_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Este ingreso no tiene un paciente asociado para compartir el recibo.',
            ], 422);
        }

        $conn = $whatsApp->validateConnection();
        if (! ($conn['ok'] ?? false)) {
            return response()->json([
                'success' => false,
                'message' => $conn['message'] ?? 'WhatsApp no está disponible.',
            ], 422);
        }

        $payment->load(['patient']);
        $recipient = $whatsApp->validateRecipient($payment->patient?->phone);
        if (! ($recipient['ok'] ?? false)) {
            Log::info('WhatsApp income receipt share blocked', [
                'payment_id' => $payment->id,
                'patient_id' => $payment->patient_id,
                'error_code' => $recipient['error_code'] ?? null,
            ]);

            return response()->json([
                'success' => false,
                'message' => $recipient['message'] ?? 'El número de teléfono no es válido.',
            ], 422);
        }

        $pdf = Pdf::loadView('receipts.income-whatsapp', compact('payment'))
            ->setPaper('a5')
            ->setOption('isRemoteEnabled', false)
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('chroot', public_path());

        $base64 = base64_encode($pdf->output());
        $filename = 'Recibo-' . $payment->receipt_number . '.pdf';
        $caption = 'Recibo #' . $payment->receipt_number;

        $result = $whatsApp->sendMediaFile($recipient['phone'], $base64, $filename, $caption);

        if (($result['success'] ?? false) === true) {
            return response()->json([
                'success' => true,
                'message' => 'Recibo enviado por WhatsApp.',
            ]);
        }

        $friendly = match ($result['error'] ?? '') {
            'not_configured' => 'WhatsApp no está configurado correctamente.',
            default          => 'No se pudo enviar el recibo. Intentá nuevamente.',
        };

        return response()->json([
            'success' => false,
            'message' => $friendly,
        ], 422);
    }

    /**
     * Construir descripción del cierre de caja
     *
     * @param array $validated
     * @param Carbon $closingDateTime
     * @param float $currentBalance
     * @return string
     */
    private function buildClosingDescription($validated, $closingDateTime, $currentBalance)
    {
        $description = 'Cierre de caja del día ' . $closingDateTime->format('d/m/Y');
        $description .= ' - Efectivo contado: $'.number_format($validated['closing_amount'], 2);
        $description .= ' - Saldo retirado: $'.number_format($currentBalance, 2);

        // Si se cierra en un día diferente al de apertura, agregar nota de auditoría
        if ($closingDateTime->format('Y-m-d') !== now()->format('Y-m-d')) {
            $description .= ' (cerrado el ' . now()->format('d/m/Y H:i') . ')';
        }

        if (!empty($validated['notes'])) {
            $description .= ' - ' . $validated['notes'];
        }

        return $description;
    }
}
