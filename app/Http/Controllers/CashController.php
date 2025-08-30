<?php

namespace App\Http\Controllers;

use App\Models\CashMovement;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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
        
        $movements = $query->orderBy('movement_date', 'desc')
                          ->orderBy('created_at', 'desc')
                          ->get();
        
        $inflows = $movements->where('amount', '>', 0)->sum('amount');
        $outflows = $movements->where('amount', '<', 0)->sum('amount');
        $finalBalance = $initialBalance + $inflows + $outflows;
        
        $lastMovement = $movements->first();
        $systemFinalBalance = $lastMovement ? $lastMovement->balance_after : $initialBalance;
        
        $cashSummary = [
            'date' => $selectedDate,
            'initial_balance' => $initialBalance,
            'total_inflows' => $inflows,
            'total_outflows' => abs($outflows),
            'final_balance' => $finalBalance,
            'system_final_balance' => $systemFinalBalance,
            'is_closed' => false, // Sin funcionalidad de cierre por ahora
            'movements_count' => $movements->count()
        ];
        
        $movementsByType = $movements->groupBy('type')->map(function ($group, $type) {
            return [
                'type' => $type,
                'inflows' => $group->where('amount', '>', 0)->sum('amount'),
                'outflows' => abs($group->where('amount', '<', 0)->sum('amount')),
                'count' => $group->count()
            ];
        });
        
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'summary' => $cashSummary,
                'movements' => $movements,
                'movements_by_type' => $movementsByType
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
            'period_days' => $startDate->diffInDays($endDate) + 1
        ];
        
        $movementsByType = $movements->groupBy('type')->map(function ($group, $type) {
            return [
                'type' => $type,
                'inflows' => $group->where('amount', '>', 0)->sum('amount'),
                'outflows' => abs($group->where('amount', '<', 0)->sum('amount')),
                'count' => $group->count()
            ];
        });
        
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'report_data' => $reportData,
                'summary' => $summary,
                'movements_by_type' => $movementsByType
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
                'other' => 'Otros'
            ];
            
            return view('cash.expense-form', compact('expenseCategories'));
        }
        
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|max:500',
            'category' => 'required|string|in:office_supplies,medical_supplies,services,maintenance,taxes,professional_payments,other',
            'receipt_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'notes' => 'nullable|string|max:500'
        ]);
        
        try {
            DB::beginTransaction();
            
            $receiptPath = null;
            if ($request->hasFile('receipt_file')) {
                $receiptPath = $request->file('receipt_file')->store('cash/receipts', 'public');
            }
            
            $lastMovement = CashMovement::orderBy('movement_date', 'desc')
                ->orderBy('created_at', 'desc')
                ->first();
            
            $currentBalance = $lastMovement ? $lastMovement->balance_after : 0;
            $newBalance = $currentBalance - $validated['amount'];
            
            $description = $validated['description'];
            if ($validated['notes']) {
                $description .= ' - ' . $validated['notes'];
            }
            
            $cashMovement = CashMovement::create([
                'movement_date' => now(),
                'type' => 'expense',
                'amount' => -$validated['amount'], // Negativo para egreso
                'description' => $description,
                'reference_type' => 'expense_category',
                'reference_id' => null,
                'balance_after' => $newBalance,
                'user_id' => auth()->id(),
            ]);
            
            DB::commit();
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Gasto registrado exitosamente.',
                    'cash_movement' => $cashMovement
                ]);
            }
            
            return redirect()->route('cash.daily')
                ->with('success', 'Gasto registrado exitosamente.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al registrar el gasto: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()
                ->withErrors(['error' => 'Error al registrar el gasto: ' . $e->getMessage()])
                ->withInput();
        }
    }
    
    public function getCashMovementDetails(CashMovement $cashMovement)
    {
        $cashMovement->load(['user']);
        
        return response()->json([
            'success' => true,
            'cash_movement' => $cashMovement
        ]);
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
                        'count' => $dayMovements->count()
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
                        'period_label' => 'Semana del ' . $period->format('d/m') . ' al ' . $weekEnd->format('d/m/Y'),
                        'inflows' => $weekMovements->where('amount', '>', 0)->sum('amount'),
                        'outflows' => abs($weekMovements->where('amount', '<', 0)->sum('amount')),
                        'net' => $weekMovements->sum('amount'),
                        'count' => $weekMovements->count()
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
                        'count' => $monthMovements->count()
                    ]);
                    
                    $period->addMonth();
                }
                break;
        }
        
        return $data;
    }
}