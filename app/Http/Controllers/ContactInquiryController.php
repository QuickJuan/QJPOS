<?php

namespace App\Http\Controllers;

use App\Mail\ContactInquirySubmitted;
use App\Models\ContactInquiry;
use App\Settings\GeneralSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use App\Models\User;

class ContactInquiryController extends Controller
{
    public function store(Request $request)
    {
        // reCAPTCHA v3 verification — skip on local or when no secret key is configured
        $secret = config('services.recaptcha.secret');
        if (! app()->environment('local') && $secret) {
            $request->validate([
                'recaptcha_token' => ['required', 'string'],
            ], [
                'recaptcha_token.required' => 'reCAPTCHA verification failed. Please try again.',
            ]);

            $verify = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret'   => $secret,
                'response' => $request->input('recaptcha_token'),
                'remoteip' => $request->ip(),
            ]);

            $score = $verify->json('score', 0);
            $threshold = (float) config('services.recaptcha.threshold', 0.5);

            if (! $verify->json('success') || $score < $threshold) {
                return response()->json([
                    'message' => 'reCAPTCHA verification failed. Please try again.',
                ], 422);
            }
        }

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
        $viewUrl = '/admin/contact-inquiries/' . $inquiry->id;
        foreach ($users as $user) {
            Notification::make()
                ->title('New Contact Inquiry')
                ->body(($inquiry->name ?: 'Someone') . ' sent a message')
                ->icon('heroicon-o-envelope')
                ->iconColor('info')
                ->actions([
                    Action::make('view')
                        ->label('View Inquiry')
                        ->url($viewUrl),
                ])
                ->sendToDatabase($user);
        }

        return response()->json(['message' => 'Submitted']);
    }
}
