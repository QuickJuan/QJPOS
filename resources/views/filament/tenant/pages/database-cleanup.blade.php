<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Warning Banner -->
        <div class="rounded-lg bg-warning-50 dark:bg-warning-400/10 p-4 border border-warning-200 dark:border-warning-400/20">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-warning-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-warning-800 dark:text-warning-300">
                        Warning: Permanent Data Deletion
                    </h3>
                    <div class="mt-2 text-sm text-warning-700 dark:text-warning-400">
                        <p>This action will permanently delete all transaction data including carts, orders, payments, and cashier sessions. This action cannot be undone.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Cart Items</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-white mt-1">{{ number_format($stats['cart_items'] ?? 0) }}</p>
                    </div>
                    <div class="p-3 bg-blue-100 dark:bg-blue-900/20 rounded-full">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Carts</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-white mt-1">{{ number_format($stats['carts'] ?? 0) }}</p>
                    </div>
                    <div class="p-3 bg-green-100 dark:bg-green-900/20 rounded-full">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Order Items</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-white mt-1">{{ number_format($stats['order_items'] ?? 0) }}</p>
                    </div>
                    <div class="p-3 bg-purple-100 dark:bg-purple-900/20 rounded-full">
                        <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Orders</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-white mt-1">{{ number_format($stats['orders'] ?? 0) }}</p>
                    </div>
                    <div class="p-3 bg-yellow-100 dark:bg-yellow-900/20 rounded-full">
                        <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Payments</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-white mt-1">{{ number_format($stats['payments'] ?? 0) }}</p>
                    </div>
                    <div class="p-3 bg-red-100 dark:bg-red-900/20 rounded-full">
                        <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Cashier Sessions</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-white mt-1">{{ number_format($stats['cashier_sessions'] ?? 0) }}</p>
                    </div>
                    <div class="p-3 bg-indigo-100 dark:bg-indigo-900/20 rounded-full">
                        <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Card -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                Cleanup Transaction Data
            </h3>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
                This will permanently delete all transaction data and reset auto-increment counters. Use this feature carefully as it cannot be undone.
            </p>

            <div class="flex items-center gap-3">
                <x-filament::button
                    color="danger"
                    wire:click="cleanupTransactionData"
                    wire:loading.attr="disabled"
                    wire:confirm="Are you sure you want to delete ALL transaction data? This action cannot be undone!"
                >
                    <span wire:loading.remove wire:target="cleanupTransactionData">
                        Delete All Transaction Data
                    </span>
                    <span wire:loading wire:target="cleanupTransactionData">
                        Processing...
                    </span>
                </x-filament::button>

                <x-filament::button
                    color="gray"
                    wire:click="loadStats"
                    wire:loading.attr="disabled"
                    outlined
                >
                    <span wire:loading.remove wire:target="loadStats">
                        Refresh Statistics
                    </span>
                    <span wire:loading wire:target="loadStats">
                        Loading...
                    </span>
                </x-filament::button>
            </div>
        </div>

        <!-- Info Panel -->
        <div class="rounded-lg bg-info-50 dark:bg-info-400/10 p-4 border border-info-200 dark:border-info-400/20">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-info-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a.75.75 0 000 1.5h.253a.25.25 0 01.244.304l-.459 2.066A1.75 1.75 0 0010.747 15H11a.75.75 0 000-1.5h-.253a.25.25 0 01-.244-.304l.459-2.066A1.75 1.75 0 009.253 9H9z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-info-800 dark:text-info-300">
                        Information
                    </h3>
                    <div class="mt-2 text-sm text-info-700 dark:text-info-400">
                        <ul class="list-disc pl-5 space-y-1">
                            <li>This cleanup affects only the current tenant database</li>
                            <li>Product, customer, and user data will not be affected</li>
                            <li>The operation is wrapped in a transaction and will rollback on error</li>
                            <li>Tables will be truncated to reset auto-increment IDs</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
