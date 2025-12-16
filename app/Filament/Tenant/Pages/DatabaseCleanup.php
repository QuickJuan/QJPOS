<?php

namespace App\Filament\Tenant\Pages;

use Filament\Pages\Page;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use App\Models\Cart;
use App\Models\Order;
use App\Models\CashierSession;
use App\Models\Payment;

class DatabaseCleanup extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-trash';

    protected static string $view = 'filament.tenant.pages.database-cleanup';

    protected static ?string $navigationLabel = 'Database Cleanup';

    protected static ?string $navigationGroup = 'Management';

    protected static ?int $navigationSort = 99;

    public $stats = [];

    public function mount(): void
    {
        $this->loadStats();
    }

    public function loadStats(): void
    {
        try {
            $this->stats = [
                'cart_items' => DB::table('cart_items')->count(),
                'carts' => Cart::count(),
                'order_items' => DB::table('order_items')->count(),
                'orders' => Order::count(),
                'payments' => Payment::count(),
                'cashier_sessions' => CashierSession::count(),
            ];
        } catch (\Exception $e) {
            $this->stats = [
                'cart_items' => 0,
                'carts' => 0,
                'order_items' => 0,
                'orders' => 0,
                'payments' => 0,
                'cashier_sessions' => 0,
            ];
        }
    }

    public function cleanupTransactionData(): void
    {
        $deletedCartItems = 0;
        $deletedCarts = 0;
        $deletedOrderItems = 0;
        $deletedOrders = 0;
        $deletedPayments = 0;
        $deletedCashierSessions = 0;

        try {
            // Count records before deletion
            $deletedCartItems = DB::table('cart_items')->count();
            $deletedCarts = Cart::count();
            $deletedOrderItems = DB::table('order_items')->count();
            $deletedOrders = Order::count();
            $deletedPayments = Payment::count();
            $deletedCashierSessions = CashierSession::count();

            // Disable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            // Truncate tables (this implicitly commits any active transaction)
            $tables = [
                'cart_items',
                'carts',
                'order_items',
                'orders',
                'payments',
                'cashier_sessions',
            ];

            foreach ($tables as $table) {
                DB::table($table)->truncate();
            }

            // Re-enable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            // Reload stats
            $this->loadStats();

            Notification::make()
                ->title('Database Cleanup Successful')
                ->body(sprintf(
                    'Deleted: %d cart items, %d carts, %d order items, %d orders, %d payments, %d cashier sessions',
                    $deletedCartItems,
                    $deletedCarts,
                    $deletedOrderItems,
                    $deletedOrders,
                    $deletedPayments,
                    $deletedCashierSessions
                ))
                ->success()
                ->duration(5000)
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Cleanup Failed')
                ->body('Error: ' . $e->getMessage())
                ->danger()
                ->duration(5000)
                ->send();
        }
    }    protected function getHeaderActions(): array
    {
        return [];
    }
}
