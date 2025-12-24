<?php

namespace App\Services;

use App\Models\CashierCashout;
use App\Models\CashierSession;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use RuntimeException;

class CashierCashoutService
{
    public function __construct(
        private CashierCashout $cashierCashout,
        private CashierSession $cashierSession
    ) {}

    public function getActiveSession(User $cashier): ?CashierSession
    {
        return $this->cashierSession
            ->newQuery()
            ->where('cashier_id', $cashier->id)
            ->whereNull('closing_time')
            ->latest('started_time')
            ->first();
    }

    public function createCashout(array $payload, User $cashier): CashierCashout
    {
        $session = $this->getActiveSession($cashier);

        if (! $session) {
            throw new RuntimeException('You need an active cashier session before making a cash movement.');
        }

        $type = $payload['type'] ?? CashierCashout::TYPE_CASH_OUT;

        if (! in_array($type, CashierCashout::availableTypes(), true)) {
            throw new RuntimeException('Unsupported cash movement type.');
        }

        $cashout = $this->cashierCashout->create([
            'cashier_id' => $cashier->id,
            'cashier_session_id' => $session->id,
            'type' => $type,
            'amount' => (float) $payload['amount'],
            'source_name' => $payload['source_name'] ?? null,
            'purpose' => $payload['purpose'],
            'details' => $payload['details'],
            'status' => CashierCashout::STATUS_PENDING,
            'meta' => $payload['meta'] ?? null,
        ]);

        $cashout->loadMissing(['cashier:id,name', 'session']);

        $this->notifyManagersOfNewRequest($cashout);

        return $cashout;
    }

    public function recentCashouts(CashierSession $session, int $limit = 5): Collection
    {
        return $session->cashouts()
            ->with(['cashier:id,name', 'approver:id,name'])
            ->latest()
            ->take($limit)
            ->get();
    }

    public function listCashoutsForUser(User $cashier, array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = $this->cashierCashout
            ->newQuery()
            ->with(['cashier:id,name', 'approver:id,name', 'session:id,business_date'])
            ->where('cashier_id', $cashier->id);

        $this->applyFilters($query, $filters);

        return $query->latest()->paginate($perPage)->withQueryString();
    }

    public function buildSessionSummary(CashierSession $session): array
    {
        $query = $this->cashierCashout
            ->newQuery()
            ->where('cashier_session_id', $session->id)
            ->with('cashier:id,name');

        return $this->summarizeFromBuilder($query);
    }

    public function buildUserSummary(User $cashier, array $filters = []): array
    {
        $query = $this->cashierCashout
            ->newQuery()
            ->where('cashier_id', $cashier->id)
            ->with('cashier:id,name');

        $this->applyFilters($query, $filters);

        return $this->summarizeFromBuilder($query);
    }

    public function approve(CashierCashout $cashout, User $approver, ?string $notes = null): CashierCashout
    {
        if (! $cashout->isPending()) {
            throw new RuntimeException('Only pending requests can be approved.');
        }

        $cashout->update([
            'status' => CashierCashout::STATUS_APPROVED,
            'approved_by' => $approver->id,
            'approved_at' => now(),
            'approval_notes' => $notes,
        ]);

        $cashout->loadMissing('cashier:id,name');

        $this->notifyCashierStatusChange($cashout, true, $notes);

        return $cashout->refresh();
    }

    public function reject(CashierCashout $cashout, User $approver, ?string $notes = null): CashierCashout
    {
        if (! $cashout->isPending()) {
            throw new RuntimeException('Only pending requests can be rejected.');
        }

        $cashout->update([
            'status' => CashierCashout::STATUS_REJECTED,
            'approved_by' => $approver->id,
            'approved_at' => now(),
            'approval_notes' => $notes,
        ]);

        $cashout->loadMissing('cashier:id,name');

        $this->notifyCashierStatusChange($cashout, false, $notes);

        return $cashout->refresh();
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        if (! empty($filters['status']) && in_array($filters['status'], CashierCashout::availableStatuses(), true)) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['type']) && in_array($filters['type'], CashierCashout::availableTypes(), true)) {
            $query->where('type', $filters['type']);
        }

        if (! empty($filters['search'])) {
            $search = trim((string) $filters['search']);

            $query->where(function (Builder $builder) use ($search) {
                $builder->where('purpose', 'like', "%{$search}%")
                    ->orWhere('details', 'like', "%{$search}%")
                    ->orWhere('source_name', 'like', "%{$search}%");
            });
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }
    }

    protected function summarizeFromBuilder(Builder $query): array
    {
        $approvedQuery = (clone $query)
            ->where('status', CashierCashout::STATUS_APPROVED);

        $cashOutTotal = (clone $approvedQuery)
            ->where('type', CashierCashout::TYPE_CASH_OUT)
            ->sum('amount');

        $cashInTotal = (clone $approvedQuery)
            ->where('type', CashierCashout::TYPE_CASH_IN)
            ->sum('amount');

        $pendingAmount = (clone $query)
            ->where('status', CashierCashout::STATUS_PENDING)
            ->sum('amount');

        $pendingCount = (clone $query)
            ->where('status', CashierCashout::STATUS_PENDING)
            ->count();

        $totalRecords = (clone $query)->count();

        $latest = (clone $query)
            ->latest()
            ->with('cashier:id,name')
            ->first();

        return [
            'total_cash_out' => (float) $cashOutTotal,
            'total_cash_in' => (float) $cashInTotal,
            'net_cash' => (float) ($cashInTotal - $cashOutTotal),
            'pending_amount' => (float) $pendingAmount,
            'pending_count' => (int) $pendingCount,
            'total_records' => (int) $totalRecords,
            'last_cash_movement_at' => optional($latest?->created_at)->toDateTimeString(),
            'last_cash_movement_type' => $latest?->type,
            'last_cash_movement_by' => $latest?->cashier?->name,
        ];
    }

    protected function notifyManagersOfNewRequest(CashierCashout $cashout): void
    {
        $recipients = User::role(['Admin', 'Manager'])->get();

        if ($recipients->isEmpty()) {
            return;
        }

        foreach ($recipients as $recipient) {
            if ($recipient->id === $cashout->cashier_id) {
                continue;
            }

            Notification::make()
                ->title('Cash drawer request pending approval')
                ->body(sprintf(
                    '%s submitted a %s for %s worth %s.',
                    $cashout->cashier?->name ?? 'A cashier',
                    $cashout->type === CashierCashout::TYPE_CASH_IN ? 'cash in' : 'cash out',
                    $cashout->purpose,
                    number_format((float) $cashout->amount, 2)
                ))
                ->info()
                ->sendToDatabase($recipient);
        }
    }

    protected function notifyCashierStatusChange(CashierCashout $cashout, bool $approved, ?string $notes = null): void
    {
        $cashier = $cashout->cashier;

        if (! $cashier) {
            return;
        }

        $notification = Notification::make()
            ->title($approved ? 'Cash drawer request approved' : 'Cash drawer request rejected')
            ->body(sprintf(
                'Your %s request for %s worth %s was %s%s.',
                $cashout->type === CashierCashout::TYPE_CASH_IN ? 'cash in' : 'cash out',
                $cashout->purpose,
                number_format((float) $cashout->amount, 2),
                $approved ? 'approved' : 'rejected',
                $notes ? " ({$notes})" : ''
            ));

        $approved ? $notification->success() : $notification->danger();

        $notification->sendToDatabase($cashier);
    }
}
