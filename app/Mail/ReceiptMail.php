<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReceiptMail extends Mailable
{
    use Queueable, SerializesModels;

    protected $order;

    public function __construct(Order $order)
    {
        // Load relationships to avoid lazy loading in queued job
        $this->order = $order->load([
            'orderItems.product',
            'orderItems.children.product',
            'cashierSession.branch',
            'cashier',
            'payments.paymentMethod',
            'payments.currency',
            'customer'
        ]);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Your Receipt - Invoice #{$this->order->invoice_no}",
        );
    }

    public function content(): Content
    {
        try {
            // Generate the receipt HTML file
            $receiptService = app(\App\Services\ReceiptImageService::class);
            $receiptUrl = $receiptService->generateReceiptHtml($this->order);

            $branch = $this->order->cashierSession->branch ?? null;

            return new Content(
                view: 'emails.receipt',
                with: [
                    'receiptUrl' => $receiptUrl,
                    'invoiceNo' => str_pad($this->order->invoice_no, 6, '0', STR_PAD_LEFT),
                    'orderDate' => $this->order->created_at->format('m/d/Y h:i A'),
                    'totalAmount' => $this->order->total_due,
                    'cashier' => $this->order->cashier?->name,
                    'storeName' => $branch?->name ?? 'QuickJuan POS',
                    'storeAddress' => $branch?->address,
                    'storePhone' => $branch?->phone,
                ]
            );
        } catch (\Exception $e) {
            \Log::error('Receipt email generation error: ' . $e->getMessage(), [
                'order_id' => $this->order->id,
                'trace' => $e->getTraceAsString()
            ]);

            // Return email with error state
            return new Content(
                view: 'emails.receipt',
                with: [
                    'receiptUrl' => null,
                    'invoiceNo' => str_pad($this->order->invoice_no, 6, '0', STR_PAD_LEFT),
                    'orderDate' => $this->order->created_at->format('m/d/Y h:i A'),
                    'totalAmount' => $this->order->total_due,
                    'cashier' => $this->order->cashier?->name,
                    'storeName' => 'QuickJuan POS',
                    'storeAddress' => '',
                    'storePhone' => '',
                ]
            );
        }
    }

    public function attachments(): array
    {
        return [];    }
}
