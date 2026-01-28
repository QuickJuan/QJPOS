<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Order;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class PrintSalesInvoiceReportController extends Controller
{
    public function __invoke(Request $request)
    {
        $filters = $this->normalizeFilters($request);

        $orders = Order::query()
            ->with([
                'branch',
                'cashier',
                'customer',
                'payments.paymentMethod',
                'orderItems.product',
                'orderItems.productPackaging',
            ])
            ->withCount('orderItems')
            ->when($filters['branch_id'], fn (Builder $query, int $branchId) => $query->where('branch_id', $branchId))
            ->when($filters['cashier_id'], fn (Builder $query, int $cashierId) => $query->where('cashier_id', $cashierId))
            ->when($filters['cashier_session_id'], fn (Builder $query, int $sessionId) => $query->where('cashier_session_id', $sessionId))
            ->when($filters['status'], fn (Builder $query, string $status) => $query->where('status', $status))
            ->when($filters['from'], fn (Builder $query, Carbon $from) => $query->whereDate('created_at', '>=', $from))
            ->when($filters['until'], fn (Builder $query, Carbon $until) => $query->whereDate('created_at', '<=', $until))
            ->orderBy('created_at', 'desc')
            ->get();

        return view('reports.sales-invoice-print', [
            'orders' => $orders,
            'filterSummary' => $this->buildFilterSummary($filters),
            'grandTotals' => $this->computeGrandTotals($orders),
        ]);
    }

    private function normalizeFilters(Request $request): array
    {
        $branchId = $request->filled('branch_id') ? (int) $request->input('branch_id') : null;
        $cashierId = $request->filled('cashier_id') ? (int) $request->input('cashier_id') : null;
        $cashierSessionId = $request->filled('cashier_session_id') ? (int) $request->input('cashier_session_id') : null;
        $status = $request->filled('status') ? (string) $request->input('status') : null;
        $from = $request->filled('from') ? Carbon::parse($request->input('from')) : null;
        $until = $request->filled('until') ? Carbon::parse($request->input('until')) : null;

        return [
            'branch_id' => $branchId,
            'cashier_id' => $cashierId,
            'cashier_session_id' => $cashierSessionId,
            'status' => $status,
            'from' => $from,
            'until' => $until,
        ];
    }

    private function buildFilterSummary(array $filters): array
    {
        $branchName = $filters['branch_id'] ? Branch::find($filters['branch_id'])?->name : 'All branches';
        $cashierName = $filters['cashier_id'] ? User::find($filters['cashier_id'])?->name : 'All cashiers';
        $sessionLabel = $filters['cashier_session_id'] ? 'Session #' . $filters['cashier_session_id'] : 'All cashier sessions';
        $statusLabel = $filters['status'] ? ucfirst($filters['status']) : 'All statuses';

        $from = $filters['from'] instanceof Carbon ? $filters['from'] : null;
        $until = $filters['until'] instanceof Carbon ? $filters['until'] : null;

        $dateRange = match (true) {
            $from && $until => $from->format('M d, Y') . ' - ' . $until->format('M d, Y'),
            $from => 'From ' . $from->format('M d, Y'),
            $until => 'Until ' . $until->format('M d, Y'),
            default => 'All dates',
        };

        return array_filter([
            'Branch' => $branchName,
            'Cashier' => $cashierName,
            'Cashier Session' => $sessionLabel,
            'Status' => $statusLabel,
            'Date Range' => $dateRange,
        ], fn ($value) => filled($value));
    }

    private function computeGrandTotals($orders): array
    {
        $totals = [
            'qty' => 0.0,
            'amount' => 0.0,
            'less_tax' => 0.0,
            'discount' => 0.0,
            'sales' => 0.0,
            'cost' => 0.0,
            'profit' => 0.0,
            'payment' => 0.0,
        ];

        foreach ($orders as $order) {
            foreach ($order->orderItems as $item) {
                $quantity = (float) ($item->quantity ?? 0);
                $price = (float) ($item->price ?? 0);

                $totals['qty'] += $quantity;
                $totals['amount'] += (float) ($item->amount ?? ($price * $quantity));
                $totals['less_tax'] += (float) ($item->less_tax ?? 0);
                $totals['discount'] += (float) ($item->discount_amount ?? $item->item_discount ?? 0);
                $totals['sales'] += (float) ($item->sub_total ?? $item->amount ?? 0);
                $totals['cost'] += (float) ($item->cost ?? 0);
                $totals['profit'] += (float) ($item->profit ?? 0);
            }

            $totals['payment'] += (float) ($order->payments?->sum(fn ($payment) => (float) ($payment->amount_applied ?? $payment->amount ?? 0)) ?? 0);
        }

        return $totals;
    }
}
