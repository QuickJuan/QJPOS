<?php

namespace App\Services;

use App\Http\Resources\ReceiptOrdersResource;
use App\Models\Order;
use Illuminate\Support\Facades\Storage;

class ReceiptImageService
{
    /**
     * Generate a receipt HTML file and return the public URL
     *
     * @param Order $order
     * @return string The public URL to the receipt
     */
    public function generateReceiptHtml(Order $order): string
    {
        try {
            // Load necessary relationships
            $order->load([
                'orderItems.product',
                'orderItems.children.product',
                'cashierSession.branch',
                'cashier',
                'payments.paymentMethod',
                'payments.currency',
                'customer'
            ]);

            // Format data using the same resource as thermal printer
            $orderResource = new ReceiptOrdersResource($order);
            $receiptData = $orderResource->toArray(request());

            // Generate HTML content
            $html = view('receipts.email-receipt-html', ['receiptData' => $receiptData])->render();

            // Create filename - tenant isolation handled automatically by FilesystemTenancyBootstrapper
            $filename = 'receipts/receipt-' . $order->invoice_no . '-' . time() . '.html';

            // Store in public disk (automatically tenant-scoped)
            Storage::disk('public')->put($filename, $html);

            // Generate tenant-aware URL
            $path = Storage::disk('public')->path($filename);
            $relativePath = str_replace(storage_path('app/public/'), '', $path);

            // Get current request URL to build tenant URL
            $currentUrl = request()->getSchemeAndHttpHost();

            return $currentUrl . '/storage/' . $relativePath;
        } catch (\Exception $e) {
            \Log::error('Receipt generation error: ' . $e->getMessage(), [
                'order_id' => $order->id,
                'invoice_no' => $order->invoice_no,
                'tenant_id' => tenant('id'),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Clean up old receipt files (older than 7 days)
     * Automatically scoped to current tenant
     *
     * @return void
     */
    public function cleanupOldReceipts(): void
    {
        // This will automatically work within tenant context
        $files = Storage::disk('public')->files('receipts');
        $weekAgo = now()->subDays(7)->timestamp;

        foreach ($files as $file) {
            if (Storage::disk('public')->lastModified($file) < $weekAgo) {
                Storage::disk('public')->delete($file);
            }
        }
    }
}
