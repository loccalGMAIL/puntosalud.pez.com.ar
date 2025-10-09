<?php

namespace App\Http\Controllers;

use App\Models\CashMovement;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CashController extends Controller
{
    public function dailyCash(Request $request)
    {
        $date = $request->get('date', now()->format('Y-m-d'));
        $selectedDate = Carbon::parse($date);

        $previousDay = $selectedDate->copy()->subDay();
        $lastBalanceMovement = CashMovement::whereDate('movement_date', '<=', $previousDay)
            ->orderBy('movement_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->first();

        $initialBalance = $lastBalanceMovement ? $lastBalanceMovement->balance_after : 0;

        $query = CashMovement::with(['user'])
            ->whereDate('movement_date', $selectedDate);

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('reference_type')) {
            $query->where('reference_type', $request->reference_type);
        }

        $movements = $query->orderBy('created_at', 'desc')
            ->get();

        $inflows = $movements->where('amount', '>', 0)->sum('amount');
        $outflows = $movements->where('amount', '<', 0)->sum('amount');
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

        $movementsByType = $movements->groupBy('type')->map(function ($group, $type) {
            return [
                'type' => $type,
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

    public function cashReport(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));
        $groupBy = $request->get('group_by', 'day');

        $startDate = Carbon::parse($dateFrom);
        $endDate = Carbon::parse($dateTo);

        $movements = CashMovement::with(['user'])
            ->whereDate('movement_date', '>=', $startDate)
            ->whereDate('movement_date', '<=', $endDate)
            ->orderBy('movement_date')
            ->get();

        $reportData = $this->generateReportData($movements, $groupBy, $startDate, $endDate);

        $summary = [
            'total_inflows' => $movements->where('amount', '>', 0)->sum('amount'),
            'total_outflows' => abs($movements->where('amount', '<', 0)->sum('amount')),
            'net_amount' => $movements->sum('amount'),
            'movements_count' => $movements->count(),
            'period_days' => $startDate->diffInDays($endDate) + 1,
        ];

        $movementsByType = $movements->groupBy('type')->map(function ($group, $type) {
            return [
                'type' => $type,
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

        return view('cash.report', compact('reportData', 'summary', 'movementsByType'));
    }

    public function addExpense(Request $request)
    {
        if ($request->isMethod('get')) {
            $expenseCategories = [
                'office_supplies' => 'Insumos de Oficina',
                'medical_supplies' => 'Insumos Médicos',
                'services' => 'Servicios',
                'maintenance' => 'Mantenimiento',
                'taxes' => 'Impuestos',
                'professional_payments' => 'Pagos a Profesionales',
                'patient_refund' => 'Reintegro/Devolución a Paciente',
                'other' => 'Otros',
            ];

            // Obtener profesionales activos que tengan turnos hoy y no estén liquidados
            $today = now()->format('Y-m-d');

            $professionals = \App\Models\Professional::active()
                ->whereHas('appointments', function ($query) use ($today) {
                    $query->whereDate('appointment_date', $today);
                })
                ->whereDoesntHave('liquidations', function ($query) use ($today) {
                    $query->where('liquidation_date', $today)
                          ->where('payment_status', 'paid');
                })
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->get();

            return view('cash.expense-form', compact('expenseCategories', 'professionals'));
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|max:500',
            'category' => 'required|string|in:office_supplies,medical_supplies,services,maintenance,taxes,professional_payments,patient_refund,other',
            'professional_id' => 'nullable|exists:professionals,id|required_if:category,patient_refund',
            'receipt_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            $receiptPath = null;
            if ($request->hasFile('receipt_file')) {
                $receiptPath = $request->file('receipt_file')->store('cash/receipts', 'public');
            }

            // Obtener balance actual con lock pesimista
            $currentBalance = CashMovement::getCurrentBalanceWithLock();
            $newBalance = $currentBalance - $validated['amount'];

            $description = $validated['description'];

            // Si es devolución a paciente, agregar nombre del profesional a la descripción
            if ($validated['category'] === 'patient_refund' && isset($validated['professional_id'])) {
                $professional = \App\Models\Professional::find($validated['professional_id']);
                if ($professional) {
                    $description = "Reintegro a Paciente - Dr. {$professional->first_name} {$professional->last_name} - " . $description;
                }
            }

            if ($validated['notes']) {
                $description .= ' - '.$validated['notes'];
            }

            $cashMovement = CashMovement::create([
                'movement_date' => now(),
                'type' => 'expense',
                'amount' => -$validated['amount'], // Negativo para egreso
                'description' => $description,
                'reference_type' => isset($validated['professional_id']) ? 'App\\Models\\Professional' : 'expense_category',
                'reference_id' => $validated['professional_id'] ?? null,
                'balance_after' => $newBalance,
                'user_id' => auth()->id(),
            ]);

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Gasto registrado exitosamente.',
                    'cash_movement' => $cashMovement,
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
        $cashMovement->load(['user']);

        $additionalData = [];

        try {
            // Cargar información adicional según el tipo de movimiento y reference_type
            if ($cashMovement->type === 'patient_payment' && $cashMovement->reference_id &&
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
                    if ($cashMovement->type === 'professional_payment') {
                        $additionalData['professional'] = $professional;
                    } elseif ($cashMovement->type === 'expense') {
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
            $lastMovement = CashMovement::whereDate('movement_date', '<=', $previousDay)
                ->orderBy('movement_date', 'desc')
                ->orderBy('created_at', 'desc')
                ->lockForUpdate()
                ->first();

            $previousBalance = $lastMovement ? $lastMovement->balance_after : 0;
            $openingAmount = $validated['opening_amount'] ?? 0;
            $newBalance = $previousBalance + $openingAmount;

            // Crear movimiento de apertura
            $cashMovement = CashMovement::create([
                'movement_date' => now(),
                'type' => 'cash_opening',
                'amount' => $openingAmount,
                'description' => 'Apertura de caja - '.($validated['notes'] ?: 'Apertura del día'),
                'reference_type' => 'cash_opening',
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

            $closeDate = $validated['close_date'] ? Carbon::parse($validated['close_date']) : now()->startOfDay();

            // Verificar que exista apertura para esa fecha
            $opening = CashMovement::forDate($closeDate)->openingMovements()->first();
            if (! $opening) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró apertura de caja para la fecha especificada.',
                ], 400);
            }

            // Verificar que no exista ya un cierre
            $existingClosing = CashMovement::forDate($closeDate)->closingMovements()->first();
            if ($existingClosing) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ya existe un cierre de caja para esa fecha.',
                ], 400);
            }

            // Obtener el saldo actual antes del cierre con lock pesimista
            $lastMovement = CashMovement::whereDate('movement_date', '<=', $closeDate)
                ->orderBy('movement_date', 'desc')
                ->orderBy('created_at', 'desc')
                ->lockForUpdate()
                ->first();

            $currentBalance = $lastMovement ? $lastMovement->balance_after : 0;

            // Crear movimiento de cierre que retira todo el saldo
            $cashMovement = CashMovement::create([
                'movement_date' => $closeDate->endOfDay(),
                'type' => 'cash_closing',
                'amount' => -$currentBalance, // Retirar todo el saldo actual
                'description' => 'Cierre de caja - Efectivo contado: $'.number_format($validated['closing_amount'], 2).
                               ' - Saldo retirado: $'.number_format($currentBalance, 2).
                               ($validated['notes'] ? ' - '.$validated['notes'] : ''),
                'reference_type' => 'cash_closing',
                'reference_id' => null,
                'balance_after' => 0, // Balance queda en cero
                'user_id' => auth()->id(),
            ]);

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
        $lastBalanceMovement = CashMovement::whereDate('movement_date', '<=', $previousDay)
            ->orderBy('movement_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->first();

        $initialBalance = $lastBalanceMovement ? $lastBalanceMovement->balance_after : 0;

        // Obtener todos los movimientos del día
        $movements = CashMovement::with(['user'])
            ->whereDate('movement_date', $selectedDate)
            ->orderBy('movement_date')
            ->orderBy('created_at')
            ->get();

        // Calcular totales (excluyendo apertura y cierre de caja)
        $movementsForTotals = $movements->whereNotIn('type', ['cash_opening', 'cash_closing']);
        $inflows = $movementsForTotals->where('amount', '>', 0)->sum('amount');
        $outflows = $movementsForTotals->where('amount', '<', 0)->sum('amount');
        $finalBalance = $initialBalance + $inflows + $outflows;

        // Obtener movimientos de apertura y cierre
        $openingMovement = $movements->where('type', 'cash_opening')->first();
        $closingMovement = $movements->where('type', 'cash_closing')->first();

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
            ->whereNotIn('type', ['cash_opening', 'cash_closing'])
            ->groupBy('type')
            ->map(function ($group, $type) {
                return [
                    'type' => $type,
                    'inflows' => $group->where('amount', '>', 0)->sum('amount'),
                    'outflows' => abs($group->where('amount', '<', 0)->sum('amount')),
                    'count' => $group->count(),
                ];
            });

        // Resumen por usuario - simplificado para debug
        $userSummary = collect(); // Temporalmente vacío para evitar errores

        return view('cash.daily-report', compact(
            'selectedDate',
            'summary',
            'movements',
            'movementsByType',
            'userSummary'
        ));
    }

    public function withdrawalForm(Request $request)
    {
        if ($request->isMethod('get')) {
            $withdrawalTypes = [
                'bank_deposit' => 'Depósito Bancario',
                'expense_payment' => 'Pago de Gastos',
                'professional_liquidation' => 'Liquidación de Profesional',
                'safe_custody' => 'Custodia en Caja Fuerte',
                'other_withdrawal' => 'Otro Retiro',
            ];

            return view('cash.withdrawal-form', compact('withdrawalTypes'));
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'withdrawal_type' => 'required|string|in:bank_deposit,expense_payment,professional_liquidation,safe_custody,other_withdrawal',
            'description' => 'required|string|max:500',
            'recipient' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            DB::beginTransaction();

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

            $cashMovement = CashMovement::create([
                'movement_date' => now(),
                'type' => 'cash_withdrawal',
                'amount' => -$validated['amount'], // Negativo para salida
                'description' => $description,
                'reference_type' => $validated['withdrawal_type'],
                'reference_id' => null,
                'balance_after' => $newBalance,
                'user_id' => auth()->id(),
            ]);

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Retiro registrado exitosamente.',
                    'cash_movement' => $cashMovement,
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
            $incomeCategories = [
                'professional_module_payment' => 'Pago Módulo Profesional',
                'correction' => 'Corrección de Ingreso',
                'product_sale' => 'Venta de Producto',
                'service_fee' => 'Cobro de Servicio Extra',
                'other' => 'Otros Ingresos',
            ];

            // Obtener profesionales activos que tengan turnos hoy
            $today = now()->format('Y-m-d');

            $professionals = \App\Models\Professional::active()
                ->whereHas('appointments', function ($query) use ($today) {
                    $query->whereDate('appointment_date', $today);
                })
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->get();

            return view('cash.manual-income-form', compact('incomeCategories', 'professionals'));
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'category' => 'required|string|in:professional_module_payment,correction,product_sale,service_fee,other',
            'description' => 'required|string|max:500',
            'professional_id' => 'nullable|exists:professionals,id|required_if:category,professional_module_payment',
            'receipt_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            $receiptPath = null;
            if ($request->hasFile('receipt_file')) {
                $receiptPath = $request->file('receipt_file')->store('receipts', 'public');
            }

            // Obtener balance actual con lock pesimista
            $currentBalance = CashMovement::getCurrentBalanceWithLock();
            $newBalance = $currentBalance + $validated['amount'];

            // Construir descripción con categoría
            $categoryLabels = [
                'professional_module_payment' => 'Pago Módulo Profesional',
                'correction' => 'Corrección de Ingreso',
                'product_sale' => 'Venta de Producto',
                'service_fee' => 'Cobro de Servicio Extra',
                'other' => 'Otros Ingresos',
            ];

            $description = '['.$categoryLabels[$validated['category']].'] '.$validated['description'];

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

            $cashMovement = CashMovement::create([
                'movement_date' => now(),
                'type' => 'other',
                'amount' => $validated['amount'],
                'description' => $description,
                'reference_type' => isset($validated['professional_id']) ? 'App\\Models\\Professional' : 'manual_income',
                'reference_id' => $validated['professional_id'] ?? null,
                'balance_after' => $newBalance,
                'user_id' => auth()->id(),
            ]);

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Ingreso manual registrado exitosamente.',
                    'cash_movement' => $cashMovement,
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

    private function generateReportData($movements, $groupBy, $startDate, $endDate)
    {
        $data = collect();

        switch ($groupBy) {
            case 'day':
                $period = $startDate->copy();
                while ($period->lte($endDate)) {
                    $dayMovements = $movements->filter(function ($movement) use ($period) {
                        return Carbon::parse($movement->movement_date)->isSameDay($period);
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
                        $moveDate = Carbon::parse($movement->movement_date);

                        return $moveDate->between($period, $weekEnd);
                    });

                    $data->push([
                        'period' => $period->format('Y-m-d'),
                        'period_label' => 'Semana del '.$period->format('d/m').' al '.$weekEnd->format('d/m/Y'),
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
                        $moveDate = Carbon::parse($movement->movement_date);

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
