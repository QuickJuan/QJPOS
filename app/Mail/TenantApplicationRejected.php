<?php

namespace App\Mail;

use App\Models\Central\TenantApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TenantApplicationRejected extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        protected TenantApplication $application,
        protected string $rejectionReason = ''
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your StorePos Application Status - We\'ll Keep You in Mind',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.tenant-application-rejected',
            with: [
                'application' => $this->application,
                'businessName' => $this->application->business_name,
                'ownerName' => $this->application->owner_name,
                'rejectionReason' => $this->rejectionReason,
            ],
        );
    }
}
