<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTenantApplicationRequest;
use App\Mail\TenantApplicationReceived;
use App\Models\Central\TenantApplication;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class TenantRegistrationController extends Controller
{
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
    public function store(StoreTenantApplicationRequest $request)
    {
        $validated = $request->validated();

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
                'accept_terms' => (bool) $validated['accept_terms'],
                'accept_privacy' => (bool) $validated['accept_privacy'],
                'accept_promotions' => (bool) $validated['accept_promotions'] ?? false,
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
