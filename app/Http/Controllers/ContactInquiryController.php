<?php

namespace App\Http\Controllers;

use App\Mail\ContactInquirySubmitted;
use App\Models\ContactInquiry;
use App\Settings\GeneralSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Filament\Notifications\Notification;
use App\Models\User;

class ContactInquiryController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'message' => ['nullable', 'string'],
            'fields' => ['array'],
            'fields.*' => ['nullable'],
        ]);

        $inquiry = ContactInquiry::create([
            'name' => $data['name'] ?? null,
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'message' => $data['message'] ?? null,
            'fields' => $data['fields'] ?? null,
            'meta' => [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ],
        ]);

        // Email recipients from settings (CSV)
        $settings = app(GeneralSettings::class);
        $recipients = collect(explode(',', (string) ($settings->contact_recipient_emails ?? '')))
            ->map(fn ($email) => trim($email))
            ->filter(fn ($email) => filter_var($email, FILTER_VALIDATE_EMAIL))
            ->values();

        if ($recipients->isNotEmpty()) {
            Mail::to($recipients->all())->send(new ContactInquirySubmitted($inquiry));
        }

        // Notify Filament users (Admin/Manager)
        $users = User::role(['Admin', 'Manager'])->get();
        foreach ($users as $user) {
            Notification::make()
                ->title('New Contact Inquiry')
                ->body(($inquiry->name ?: 'Someone') . ' sent a message')
                ->sendToDatabase($user);
        }

        return response()->json([
            'message' => 'Submitted',
        ]);
    }
}
