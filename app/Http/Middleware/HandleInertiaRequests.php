<?php
namespace App\Http\Middleware;

use App\Models\CashierSession;
use App\Services\DiscountService;
use App\Services\GeneralSettingsService;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{

    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $activeBranch = $this->getActiveBranch($request);

        return array_merge(parent::share($request), [
            'flash'          => [
                'success' => fn() => $request->session()->get('success'),
                'error'   => fn()   => $request->session()->get('error'),
            ],
            'active_branch' => $activeBranch,
            'auth' => [
                'user' => $request->user(),
            ],
            // Receipt Configuration
            'receipt_headers' => fn() => $activeBranch['receipt_headers'] ?? [],
            'receipt_footers' => fn() => $activeBranch['receipt_footer'] ?? [],
            'bill_footer' => fn() => $activeBranch['receipt_footer'] ?? [],
            // Company Information
            'company_info' => fn() => $this->getCompanyInfo(),
        ]);
    }

    /**
     * Get active branch from session
     */
    private function getActiveBranch(Request $request): ?array
    {
        $activeBranch = $request->session()->get('active_branch');

        return $activeBranch ? $activeBranch->toArray() : null;
    }

    /**
     * Get company information from general settings
     */
    private function getCompanyInfo(): array
    {
        try {
            $companySettings = app(GeneralSettingsService::class)->getCompanySettings();
            return [
                'name' => $companySettings['company_name'] ?? '',
                'address' => $companySettings['company_address'] ?? '',
                'contact' => $companySettings['company_contact'] ?? '',

            ];
        } catch (\Exception $e) {
            return [
                'name' => '',
                'address' => '',
                'contact' => '',
            ];
        }
    }
}
