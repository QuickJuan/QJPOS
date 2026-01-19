<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Page;

class PagePolicy
{
    /**
     * Determine if the user can view the page.
     */
    public function view(User $user, Page $page): bool
    {
        return true; // Allow viewing in edit mode
    }

    /**
     * Determine if the user can create pages.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('admin') || $user->hasPermissionTo('create pages');
    }

    /**
     * Determine if the user can update the page.
     */
    public function update(User $user, Page $page): bool
    {
        return $user->hasRole('admin') || $user->hasPermissionTo('edit pages') || $user->id === $page->created_by;
    }

    /**
     * Determine if the user can delete the page.
     */
    public function delete(User $user, Page $page): bool
    {
        return $user->hasRole('admin') || $user->hasPermissionTo('delete pages');
    }

    /**
     * Determine if the user can publish the page.
     */
    public function publish(User $user, Page $page): bool
    {
        return $user->hasRole('admin') || $user->hasPermissionTo('publish pages');
    }
}
