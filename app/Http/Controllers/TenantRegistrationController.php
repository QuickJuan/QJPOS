<?php

namespace App\Http\Controllers;

use App\Mail\TenantApplicationReceived;
use App\Models\Central\TenantApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class TenantRegistrationController extends Controller
{
    /**
     * Validation rules for tenant application
     */
    private function validationRules(): array
    {
        return [
            'business_name' => 'required|string|max:255|min:2',
            'business_address' => 'required|string|max:500|min:5',
            'owner_name' => 'required|string|max:255|min:2',
            'owner_email' => 'required|email|max:255',
            'owner_phone' => 'required|string|max:20|min:7',
            'business_permit_number' => 'nullable|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ];
    }

    /**
     * Show the tenant registration form
     */
    public function show()
    {
        return Inertia::render('Auth/TenantRegistration');
    }

    /**
     * Handle tenant registration submission
     */
    public function store(Request $request)
    {
        $validated = $request->validate($this->validationRules());

        try {
            // Create tenant application
            $application = TenantApplication::create([
                'business_name' => trim($validated['business_name']),
                'business_address' => trim($validated['business_address']),
                'owner_name' => trim($validated['owner_name']),
                'owner_email' => strtolower(trim($validated['owner_email'])),
                'owner_phone' => trim($validated['owner_phone']),
                'business_permit_number' => trim($validated['business_permit_number'] ?? ''),
                'status' => 'pending',
            ]);

            // Handle logo upload with media library
            if ($request->hasFile('logo')) {
                $application->addMediaFromRequest('logo')
                    ->toMediaCollection('logo');
            }

            // Send confirmation email
            $this->sendConfirmationEmail($application);

            return redirect()->route('tenant-registration-success')
                ->with('success', 'Application submitted successfully!');
        } catch (\Exception $e) {
            Log::error('Tenant registration error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'email' => $validated['owner_email'] ?? 'unknown',
            ]);

            return back()->withErrors([
                'general' => 'An error occurred while processing your application. Please try again later.',
            ])->withInput();
        }
    }

    /**
     * Send confirmation email to applicant
     */
    private function sendConfirmationEmail(TenantApplication $application): void
    {
        try {
            Mail::to($application->owner_email)->send(
                new TenantApplicationReceived($application)
            );
        } catch (\Exception $e) {
            Log::warning('Failed to send confirmation email', [
                'email' => $application->owner_email,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Show success page
     */
    public function success()
    {
        return Inertia::render('Auth/TenantRegistrationSuccess');
    }
}
