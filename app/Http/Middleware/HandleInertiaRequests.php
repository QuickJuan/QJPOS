<?php
namespace App\Http\Middleware;

use App\Enums\Receipt\Type;
use App\Services\DiscountService;
use App\Services\ReceiptFooterService;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    public function __construct(
        protected ReceiptFooterService $receiptFooterService
    ) {
    }

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
        return array_merge(parent::share($request), [
            'flash'          => [
                'success' => fn() => $request->session()->get('success'),
                'error'   => fn()   => $request->session()->get('error'),
            ],
            'active_branch' => $request->session()->get('active_branch'),
            'auth' => [
                'user' => $request->user(),
            ],
            'receipt_footer' => fn() => $this->receiptFooterService->getReceiptFooter(Type::RECEIPT->value),
            'bill_footer' => fn() => $this->receiptFooterService->getReceiptFooter(Type::BILL->value),
        ]);
    }
}
