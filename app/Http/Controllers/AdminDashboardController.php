<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\CashMovement;
use App\Models\Expense;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AdminDashboardController extends Controller
{
    public function index()
    {
        if (! Auth::user()?->canAccessModule('admin_dashboard')) {
            abort(403, 'No tiene acceso a este módulo.');
        }

        $monthStart = now()->startOfMonth();
        $monthEnd = now()->endOfMonth();

        $cashMonth = DB::table('cash_movements')
            ->join('movement_types', 'cash_movements.movement_type_id', '=', 'movement_types.id')
            ->whereNotIn('movement_types.code', ['cash_opening', 'cash_closing'])
            ->whereBetween('cash_movements.created_at', [$monthStart->startOfDay(), $monthEnd->endOfDay()])
            ->selectRaw('SUM(CASE WHEN cash_movements.amount > 0 THEN cash_movements.amount ELSE 0 END) as income')
            ->selectRaw('SUM(CASE WHEN cash_movements.amount < 0 THEN ABS(cash_movements.amount) ELSE 0 END) as expenses')
            ->first();

        $externalMonthExpenses = (float) Expense::query()
            ->whereBetween('expense_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->sum('amount');

        $monthIncome = (float) ($cashMonth->income ?? 0);
        $monthCashExpenses = (float) ($cashMonth->expenses ?? 0);
        $monthTotalExpenses = $monthCashExpenses + $externalMonthExpenses;
        $monthNet = $monthIncome - $monthTotalExpenses;

        $topExpenseTypes = $this->topExpenseTypesForRange($monthStart, $monthEnd, 5);

        $flow = $this->flowLastSixMonths();

        $summary = [
            'month_label' => $monthStart->isoFormat('MMMM YYYY'),
            'income' => $monthIncome,
            'cash_expenses' => $monthCashExpenses,
            'external_expenses' => $externalMonthExpenses,
            'total_expenses' => $monthTotalExpenses,
            'net' => $monthNet,
        ];

        return view('dashboard.admin', compact(
            'summary',
            'topExpenseTypes',
            'flow'
        ));
    }

    private function topExpenseTypesForRange(Carbon $from, Carbon $to, int $limit = 5)
    {
        $cash = CashMovement::with('movementType')
            ->whereHas('movementType', fn ($q) => $q->whereIn('category', ['expense_detail', 'withdrawal_detail']))
            ->whereBetween('created_at', [$from->startOfDay(), $to->endOfDay()])
            ->get()
            ->map(fn ($m) => [
                'type' => $m->movementType,
                'amount' => abs($m->amount),
            ]);

        $external = Expense::with('movementType')
            ->whereBetween('expense_date', [$from->toDateString(), $to->toDateString()])
            ->get()
            ->map(fn ($e) => [
                'type' => $e->movementType,
                'amount' => (float) $e->amount,
            ]);

        return $cash->concat($external)
            ->groupBy(fn ($row) => $row['type']?->id)
            ->map(fn ($items) => [
                'name' => $items->first()['type']?->name ?? '-',
                'icon' => $items->first()['type']?->icon ?? '📋',
                'total' => $items->sum('amount'),
            ])
            ->sortByDesc('total')
            ->take($limit)
            ->values();
    }

    private function flowLastSixMonths(): array
    {
        $from = now()->subMonths(5)->startOfMonth();
        $to = now()->endOfMonth();

        $cashRows = DB::table('cash_movements')
            ->join('movement_types', 'cash_movements.movement_type_id', '=', 'movement_types.id')
            ->whereNotIn('movement_types.code', ['cash_opening', 'cash_closing'])
            ->whereBetween('cash_movements.created_at', [$from->startOfDay(), $to->endOfDay()])
            ->selectRaw("DATE_FORMAT(cash_movements.created_at, '%Y-%m') as month")
            ->selectRaw('SUM(CASE WHEN cash_movements.amount > 0 THEN cash_movements.amount ELSE 0 END) as income')
            ->selectRaw('SUM(CASE WHEN cash_movements.amount < 0 THEN ABS(cash_movements.amount) ELSE 0 END) as expenses')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $externalRows = Expense::query()
            ->whereBetween('expense_date', [$from->toDateString(), $to->toDateString()])
            ->selectRaw("DATE_FORMAT(expense_date, '%Y-%m') as month")
            ->selectRaw('SUM(amount) as expenses')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $cashMap = $cashRows->mapWithKeys(fn ($r) => [
            $r->month => [
                'income' => (float) $r->income,
                'expenses' => (float) $r->expenses,
            ],
        ])->toArray();

        $externalMap = $externalRows->mapWithKeys(fn ($r) => [
            $r->month => (float) $r->expenses,
        ])->toArray();

        $labels = [];
        $income = [];
        $expenses = [];
        $net = [];

        $cursor = $from->copy();
        while ($cursor <= $to) {
            $key = $cursor->format('Y-m');
            $labels[] = $cursor->isoFormat('MMM YY');

            $inc = $cashMap[$key]['income'] ?? 0.0;
            $exp = ($cashMap[$key]['expenses'] ?? 0.0) + ($externalMap[$key] ?? 0.0);

            $income[] = $inc;
            $expenses[] = $exp;
            $net[] = $inc - $exp;

            $cursor->addMonth();
        }

        return [
            'labels' => $labels,
            'income' => $income,
            'expenses' => $expenses,
            'net' => $net,
        ];
    }
}
