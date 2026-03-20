<?php

namespace App\Observers;

use App\Models\LeaveRequest;
use App\Models\User;
use App\Notifications\LeaveRequestSubmitted;

class LeaveRequestObserver
{
    public function created(LeaveRequest $leaveRequest): void
    {
        // Load relationships needed for the notification message
        $leaveRequest->load(['employee.user', 'leaveType']);

        // Notify all admin users (super_admin and admin roles)
        // Use a join-based query to avoid RoleDoesNotExist if a role isn't seeded
        User::query()
            ->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->whereIn('roles.name', ['super_admin', 'admin'])
            ->where('model_has_roles.model_type', User::class)
            ->select('users.*')
            ->distinct()
            ->get()
            ->each(function (User $admin) use ($leaveRequest) {
                // Don't notify the person who filed it (if they're an admin)
                if ($admin->id !== auth()->id()) {
                    $admin->notify(new LeaveRequestSubmitted($leaveRequest));
                }
            });
    }
}
