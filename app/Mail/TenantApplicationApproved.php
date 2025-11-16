<?php

namespace App\Mail;

use App\Models\Central\TenantApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TenantApplicationApproved extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        protected TenantApplication $application,
        protected string $subdomain = '',
        protected string $email = '',
        protected string $temporaryPassword = ''
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your StorePos Account is Ready! 🎉 Welcome Onboard',
        );
    }

    public function content(): Content
    {
        $centralDomain = config('app.url');
        // Extract domain from APP_URL (e.g., https://quickjuan.test -> quickjuan.test)
        $domain = parse_url($centralDomain, PHP_URL_HOST) ?: config('tenancy.central_domains')[0];

        return new Content(
            view: 'emails.tenant-application-approved',
            with: [
                'application' => $this->application,
                'businessName' => $this->application->business_name,
                'ownerName' => $this->application->owner_name,
                'subdomain' => $this->subdomain,
                'email' => $this->email,
                'temporaryPassword' => $this->temporaryPassword,
                'loginUrl' => "https://{$this->subdomain}.{$domain}/login",
            ],
        );
    }
}
