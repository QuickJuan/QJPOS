<?php

namespace App\Console\Commands;

use App\Models\Central\Tenant;
use App\Models\ExpensePayment;
use App\Models\User;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class NotifyDueExpenses extends Command
{
    protected $signature = 'expenses:notify-due';

    protected $description = 'Notify admins and managers about expense installments due today or tomorrow';

    public function handle(): int
    {
        $tenants = Tenant::with('domains')->get();

        foreach ($tenants as $tenant) {
            tenancy()->initialize($tenant);

            $this->notifyForDate($tenant, Carbon::today(), 'today');
            $this->notifyForDate($tenant, Carbon::tomorrow(), 'tomorrow');

            tenancy()->end();
        }

        $this->info('Expense due notifications sent.');

        return Command::SUCCESS;
    }

    private function notifyForDate(Tenant $tenant, Carbon $date, string $when): void
    {
        $payments = ExpensePayment::with('expense')
            ->where('is_paid', false)
            ->whereDate('due_date', $date)
            ->get();

        if ($payments->isEmpty()) {
            return;
        }

        $domain = $tenant->domains->first()?->domain;
        $recipients = User::role(['Admin', 'Manager'])->get();

        foreach ($payments as $payment) {
            $expense = $payment->expense;

            if (! $expense) {
                continue;
            }

            $isToday   = $when === 'today';
            $title     = $isToday ? 'Expense Due Today' : 'Expense Due Tomorrow';
            $body      = "{$expense->title} — ₱" . number_format($payment->amount, 2) . " due on {$payment->due_date->format('M d, Y')}";

            $notification = Notification::make()
                ->title($title)
                ->body($body)
                ->icon('heroicon-o-receipt-percent')
                ->iconColor($isToday ? 'danger' : 'warning');

            if ($domain) {
                $url = 'https://' . $domain . '/admin/expenses/' . $expense->id;
                $notification->actions([
                    Action::make('view')
                        ->label('View Expense')
                        ->url($url),
                ]);
            }

            foreach ($recipients as $user) {
                $notification->sendToDatabase($user);
            }
        }
    }
}
