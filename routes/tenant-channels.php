<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Tenant Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| tenant application supports. These channels are tenant-aware and will
| only be accessible within the tenant context.
|
*/

// User channel - authenticate user can access their own channel
Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Pending orders channel - tenant and branch specific
Broadcast::channel('pending-orders.{branchId}', function ($user, $branchId) {
    // Verify user belongs to the tenant (automatically handled by tenancy middleware)
    // Verify user has access to this branch
    return $user->branches()->where('branches.id', $branchId)->exists();
});

// Branch-specific notifications
Broadcast::channel('branch.{branchId}', function ($user, $branchId) {
    return $user->branches()->where('branches.id', $branchId)->exists();
});

// Kitchen display channel - for real-time order updates
Broadcast::channel('kitchen.{branchId}', function ($user, $branchId) {
    // Allow users with kitchen access to this branch
    return $user->branches()->where('branches.id', $branchId)->exists();
});
