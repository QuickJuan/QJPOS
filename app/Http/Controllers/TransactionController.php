<?php
namespace App\Http\Controllers;

use App\Enums\Receipt\Type;
use App\Models\Order;
use App\Models\User;
use App\Services\CashierSessionService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TransactionController extends Controller
{
    public function __construct(protected CashierSessionService $cashierSessionService)
    {
        $this->cashierSessionService = $cashierSessionService;
    }

    public function index(Request $request)
    {
        $orders = Order::with(['cashier', 'tableRoom', 'cashierSession', 'orderItems.product'])
            ->search($request->input('search'))
            ->dateFromFilter($request->input('date_from'))
            ->dateToFilter($request->input('date_to'))
            ->cashier($request->input('cashier_id'))
            ->status($request->input('status'))
            ->orderBy('created_at', 'desc')
            ->paginate(perPage: 5)
            ->withQueryString();

        $cashiers      = User::select('id', 'name')->orderBy('name')->get();
        $receiptFooter = $this->cashierSessionService->getReceiptFooter(Type::RECEIPT->value);

        return Inertia::render('Transactions/Index', [
            'orders'        => $orders,
            'cashiers'      => $cashiers,
            'receiptFooter' => $receiptFooter,
            'filters'       => $request->only(['search', 'date_from', 'date_to', 'status', 'cashier_id']),
        ]);
    }
}
