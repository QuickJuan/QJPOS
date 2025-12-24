<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCashierCashoutRequest;
use App\Http\Resources\CashierCashoutResource;
use App\Models\CashierCashout;
use App\Services\CashierCashoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use RuntimeException;
use Throwable;

class CashierCashoutController extends Controller
{
    public function __construct(private CashierCashoutService $cashierCashoutService) {}

    public function index(Request $request): Response
    {
        $validated = $request->validate([
            'status' => ['nullable', Rule::in(CashierCashout::availableStatuses())],
            'type' => ['nullable', Rule::in(CashierCashout::availableTypes())],
            'search' => ['nullable', 'string', 'max:255'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:50'],
        ]);

        $perPage = $validated['per_page'] ?? 10;
        unset($validated['per_page']);

        $cashouts = $this->cashierCashoutService->listCashoutsForUser(
            $request->user(),
            $validated,
            $perPage
        );

        $resource = CashierCashoutResource::collection($cashouts)
            ->response($request)
            ->getData(true);

        return Inertia::render('Resto/CashierCashouts/Index', [
            'cashouts' => $resource,
            'filters' => array_merge([
                'status' => null,
                'type' => null,
                'search' => null,
                'date_from' => null,
                'date_to' => null,
                'per_page' => $perPage,
            ], $validated),
            'summary' => $this->cashierCashoutService->buildUserSummary($request->user()),
        ]);
    }

    public function create(Request $request): Response|RedirectResponse
    {
        $session = $this->cashierCashoutService->getActiveSession($request->user());

        if (! $session) {
            return redirect()
                ->route('resto.preview')
                ->with('error', 'Start a cashier session before recording cash movements.');
        }

        $recentCashouts = $this->cashierCashoutService->recentCashouts($session, 10);
        $summary = $this->cashierCashoutService->buildSessionSummary($session);

        return Inertia::render('Resto/CashierCashouts/Create', [
            'cashierSession' => [
                'id' => $session->id,
                'business_date' => optional($session->business_date)->toDateString(),
                'started_time' => optional($session->started_time)->toDateTimeString(),
                'beginning_cash' => (float) $session->beginning_cash,
            ],
            'summary' => $summary,
            'recentCashouts' => CashierCashoutResource::collection($recentCashouts)->resolve(),
        ]);
    }

    public function store(StoreCashierCashoutRequest $request): RedirectResponse
    {
        try {
            $this->cashierCashoutService->createCashout($request->validated(), $request->user());

            return redirect()
                ->route('resto.cashier-cashouts.create')
                ->with('success', 'Cash drawer movement recorded successfully.');
        } catch (RuntimeException $exception) {
            return redirect()
                ->route('resto.preview')
                ->with('error', $exception->getMessage());
        } catch (Throwable $throwable) {
            report($throwable);

            return redirect()
                ->back()
                ->with('error', 'Unable to record cash out right now. Please try again.');
        }
    }
}
