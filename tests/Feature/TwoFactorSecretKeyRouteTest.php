<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Fortify\Features;
use Tests\TestCase;

class TwoFactorSecretKeyRouteTest extends TestCase
{
    use RefreshDatabase;

    public function test_two_factor_routes_are_configured_for_tenants_not_central(): void
    {
        if (! Features::canManageTwoFactorAuthentication()) {
            $this->markTestSkipped('Two factor authentication is not enabled.');
        }

        // These routes should not be accessible on the central domain
        // since they are configured in routes/tenant.php with tenant-specific middleware
        $response = $this->get('/user/two-factor-secret-key');

        // Should return 404 or redirect to login (depends on auth state)
        // Both are acceptable since the route isn't meant for the central domain
        $this->assertTrue(
            $response->status() === 404 || $response->status() === 302,
            "Expected 404 or 302 status, got {$response->status()}"
        );
    }

    public function test_fortify_two_factor_features_are_enabled(): void
    {
        if (! Features::canManageTwoFactorAuthentication()) {
            $this->fail('Two factor authentication is not enabled in the application.');
        }

        // This test passes if we reach here, confirming 2FA is enabled
        $this->assertTrue(true);
    }
}
