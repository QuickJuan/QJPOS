<?php
namespace App\Services;

use App\Models\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Carbon\Carbon;

class OrderService
{
    public function getOrders(array $filters = [], int $perPage = 5): LengthAwarePaginator
    {
        $query = Order::with(['cashier', 'tableRoom', 'cashierSession', 'orderItems.product']);

        // Search filter
        if (!empty($filters['search'])) {
            $query->search($filters['search']);
        }

        // Date range filters - use current date if not provided
        $dateFrom = !empty($filters['date_from']) ? $filters['date_from'] : Carbon::today()->toDateString();
        $dateTo = !empty($filters['date_to']) ? $filters['date_to'] : Carbon::today()->toDateString();

        $query->whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateTo);

        // Cashier filter
        if (!empty($filters['cashier_id'])) {
            $query->cashier($filters['cashier_id']);
        }

        // Status filter
        if (!empty($filters['status'])) {
            $query->status($filters['status']);
        }

        return $query->orderBy('id', 'desc')
            ->paginate(perPage: $perPage)
            ->withQueryString();
    }
}
