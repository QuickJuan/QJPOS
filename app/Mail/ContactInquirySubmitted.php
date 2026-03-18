<?php

namespace App\Mail;

use App\Models\ContactInquiry;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactInquirySubmitted extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public ContactInquiry $inquiry) {}

    public function build(): self
    {
        return $this
            ->subject('New Contact Inquiry')
            ->markdown('emails.contact-inquiry', [
                'inquiry' => $this->inquiry,
            ]);
    }
}
