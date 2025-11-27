<?php
namespace App\Services;

use App\Models\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class OrderService
{
    public function getOrders(array $filters = [], int $perPage = 5): LengthAwarePaginator
    {
        return Order::with(['cashier', 'tableRoom', 'cashierSession', 'orderItems.product'])
            ->search($filters['search'] ?? null)
            ->dateFromFilter($filters['date_from'] ?? null)
            ->dateToFilter($filters['date_to'] ?? null)
            ->cashier($filters['cashier_id'] ?? null)
            ->status($filters['status'] ?? null)
            ->orderBy('created_at', 'desc')
            ->paginate(perPage: $perPage)
            ->withQueryString();
    }
}
