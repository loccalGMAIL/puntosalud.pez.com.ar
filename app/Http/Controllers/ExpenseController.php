<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\MovementType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Expense::class);

        $dateFrom = $request->get('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));
        $movementTypeId = $request->get('movement_type_id');

        $query = Expense::with(['movementType', 'creator', 'updater'])
            ->forDateRange($dateFrom, $dateTo);

        if ($movementTypeId) {
            $query->byType($movementTypeId);
        }

        $expenses = $query
            ->orderBy('expense_date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(20)
            ->withQueryString();

        $expenseTypes = MovementType::active()
            ->where('category', 'expense_detail')
            ->orderBy('order')
            ->get();

        $editExpense = null;
        if ($request->filled('edit')) {
            $editExpense = Expense::with(['movementType'])->find($request->get('edit'));
        }

        return view('expenses.index', compact(
            'expenses',
            'expenseTypes',
            'dateFrom',
            'dateTo',
            'movementTypeId',
            'editExpense'
        ));
    }

    public function store(Request $request): JsonResponse
    {
        Gate::authorize('create', Expense::class);

        $validated = $request->validate([
            'expense_date' => 'required|date|before_or_equal:today',
            'movement_type_id' => 'required|exists:movement_types,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'nullable|string|in:cash,transfer,debit_card,credit_card,qr',
            'description' => 'required|string|max:500',
            'notes' => 'nullable|string',
            'receipt_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:4096',
        ]);

        $receiptPath = null;
        if ($request->hasFile('receipt_file')) {
            $receiptPath = $request->file('receipt_file')->store('expenses/receipts', 'public');
        }

        Expense::create([
            'expense_date' => $validated['expense_date'],
            'movement_type_id' => $validated['movement_type_id'],
            'amount' => $validated['amount'],
            'payment_method' => $validated['payment_method'] ?? null,
            'description' => $validated['description'],
            'notes' => $validated['notes'] ?? null,
            'receipt_path' => $receiptPath,
            'created_by' => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Gasto externo creado exitosamente.',
        ]);
    }

    public function update(Request $request, Expense $expense): JsonResponse
    {
        Gate::authorize('update', $expense);

        $validated = $request->validate([
            'expense_date' => 'required|date|before_or_equal:today',
            'movement_type_id' => 'required|exists:movement_types,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'nullable|string|in:cash,transfer,debit_card,credit_card,qr',
            'description' => 'required|string|max:500',
            'notes' => 'nullable|string',
            'receipt_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:4096',
        ]);

        $receiptPath = $expense->receipt_path;
        if ($request->hasFile('receipt_file')) {
            $receiptPath = $request->file('receipt_file')->store('expenses/receipts', 'public');
        }

        $expense->update([
            'expense_date' => $validated['expense_date'],
            'movement_type_id' => $validated['movement_type_id'],
            'amount' => $validated['amount'],
            'payment_method' => $validated['payment_method'] ?? null,
            'description' => $validated['description'],
            'notes' => $validated['notes'] ?? null,
            'receipt_path' => $receiptPath,
            'updated_by' => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Gasto externo actualizado exitosamente.',
        ]);
    }

    public function destroy(Expense $expense): JsonResponse
    {
        Gate::authorize('delete', $expense);

        $expense->delete();

        return response()->json([
            'success' => true,
            'message' => 'Gasto externo eliminado.',
        ]);
    }
}
