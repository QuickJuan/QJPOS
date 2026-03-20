<?php

namespace App\Http\Controllers;

use App\Models\EmployeeLeaveCredit;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Laravel\Fortify\Features;
use Laravel\Jetstream\Http\Controllers\Inertia\Concerns\ConfirmsTwoFactorAuthentication;
use Laravel\Jetstream\Http\Controllers\Inertia\UserProfileController as JetstreamUserProfileController;

class UserProfileController extends JetstreamUserProfileController
{
    use ConfirmsTwoFactorAuthentication;

    public function show(Request $request)
    {
        $this->validateTwoFactorAuthenticationState($request);

        $leaveCredits = $this->getLeaveCredits($request);

        return \Inertia\Inertia::render('Profile/Show', [
            'confirmsTwoFactorAuthentication' => Features::optionEnabled(Features::twoFactorAuthentication(), 'confirm'),
            'sessions'                        => $this->sessions($request)->all(),
            'leave_credits'                   => $leaveCredits,
        ]);
    }

    private function getLeaveCredits(Request $request): array
    {
        try {
            $employee = $request->user()?->employee;
            if (! $employee) {
                return [];
            }

            return EmployeeLeaveCredit::with('leaveType')
                ->where('employee_id', $employee->id)
                ->where('year', now()->year)
                ->orderBy('id')
                ->get()
                ->map(fn ($credit) => [
                    'id'              => $credit->id,
                    'leave_type'      => $credit->leaveType?->name,
                    'leave_type_code' => $credit->leaveType?->code,
                    'is_paid'         => (bool) ($credit->leaveType?->is_paid ?? true),
                    'year'            => $credit->year,
                    'total_days'      => (float) $credit->total_days,
                    'used_days'       => (float) $credit->used_days,
                    'remaining_days'  => max(0, (float) $credit->total_days - (float) $credit->used_days),
                ])
                ->values()
                ->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }
}
