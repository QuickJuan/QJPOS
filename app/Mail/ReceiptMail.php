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
        $this->order = $order->load(['orderItems', 'branch', 'cashier']);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Your Receipt - Invoice #{$this->order->invoice_no}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.receipt',
            with: [
                'order' => $this->order,
                'downloadUrl' => route('transactions.download-receipt', ['order' => $this->order->id]),
            ]
        );
    }

    public function attachments(): array
    {
        return [];    }
}
