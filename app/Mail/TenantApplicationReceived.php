<?php

namespace App\Mail;

use App\Models\Central\TenantApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TenantApplicationReceived extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(protected TenantApplication $application)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'We Received Your StorePos Application - Thank You!',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.tenant-application-received',
            with: [
                'application' => $this->application,
                'businessName' => $this->application->business_name,
                'ownerName' => $this->application->owner_name,
            ],
        );
    }
}
