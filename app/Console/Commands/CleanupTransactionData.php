<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Cart;
use App\Models\Order;
use App\Models\CashierSession;
use App\Models\Payment;

class CleanupTransactionData extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cleanup:transaction-data {database : The tenant database name} {--force : Skip confirmation prompt}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete all carts, cart items, orders, order items, cashier sessions, and payments, then truncate tables for specified tenant database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $databaseName = $this->argument('database');

        $this->line("Running cleanup for database: {$databaseName}");

        if (!$this->option('force')) {
            if (!$this->confirm('This will delete ALL transaction data (carts, orders, cashier sessions, payments) for this database. Are you sure?')) {
                $this->info('Operation cancelled.');
                return 0;
            }
        }

        return $this->cleanupDatabase($databaseName);
    }

    /**
     * Handle cleanup for specified database
     */
    protected function cleanupDatabase($databaseName)
    {
        // Set the tenant database connection
        config(['database.connections.tenant.database' => $databaseName]);

        $this->info("Starting cleanup process for database: {$databaseName}...");

        try {
            // Reconnect with the new database
            DB::purge('tenant');
            DB::reconnect('tenant');

            DB::connection('tenant')->beginTransaction();

            // Delete in proper order (children first)
            $this->deleteCartItems();
            $this->deleteCarts();

            $this->deleteOrderItems();
            $this->deleteOrders();

            $this->deletePayments();
            $this->deleteCashierSessions();

            // Truncate tables to reset auto-increment and clean up space
            $this->truncateTables();

            DB::connection('tenant')->commit();

            $this->info("✅ All transaction data for database {$databaseName} has been successfully deleted and tables truncated.");

            return 0;
        } catch (\Exception $e) {
            DB::connection('tenant')->rollBack();
            $this->error('❌ Error occurred during cleanup: ' . $e->getMessage());

            return 1;
        }
    }

    /**
     * Delete all cart items
     */
    protected function deleteCartItems()
    {
        $this->info('Deleting cart items...');
        $count = DB::connection('tenant')->table('cart_items')->count();
        DB::connection('tenant')->table('cart_items')->delete();
        $this->line("  Deleted {$count} cart items");
    }

    /**
     * Delete all carts
     */
    protected function deleteCarts()
    {
        $this->info('Deleting carts...');
        $count = Cart::count();
        Cart::query()->delete();
        $this->line("  Deleted {$count} carts");
    }

    /**
     * Delete all order items
     */
    protected function deleteOrderItems()
    {
        $this->info('Deleting order items...');
        $count = DB::connection('tenant')->table('order_items')->count();
        DB::connection('tenant')->table('order_items')->delete();
        $this->line("  Deleted {$count} order items");
    }

    /**
     * Delete all orders
     */
    protected function deleteOrders()
    {
        $this->info('Deleting orders...');
        $count = Order::count();
        Order::query()->delete();
        $this->line("  Deleted {$count} orders");
    }

    /**
     * Delete all payments
     */
    protected function deletePayments()
    {
        $this->info('Deleting payments...');
        $count = Payment::count();
        Payment::query()->delete();
        $this->line("  Deleted {$count} payments");
    }

    /**
     * Delete all cashier sessions
     */
    protected function deleteCashierSessions()
    {
        $this->info('Deleting cashier sessions...');
        $count = CashierSession::count();
        CashierSession::query()->delete();
        $this->line("  Deleted {$count} cashier sessions");
    }

    /**
     * Truncate all tables to reset auto-increment and reclaim space
     */
    protected function truncateTables()
    {
        $this->info('Truncating tables to reclaim space...');

        // Disable foreign key checks temporarily
        DB::connection('tenant')->statement('SET FOREIGN_KEY_CHECKS=0;');

        $tables = [
            'cart_items',
            'carts',
            'order_items',
            'orders',
            'payments',
            'cashier_sessions',
        ];

        foreach ($tables as $table) {
            DB::connection('tenant')->table($table)->truncate();
            $this->line("  Truncated table: {$table}");
        }

        // Re-enable foreign key checks
        DB::connection('tenant')->statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->info('✅ Tables truncated successfully');
    }
}
