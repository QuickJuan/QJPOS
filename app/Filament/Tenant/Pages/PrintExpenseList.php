<?php

namespace App\Filament\Tenant\Pages;

use App\Models\Expense;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

class PrintExpenseList extends Page
{
    protected static bool   $shouldRegisterNavigation = false;
    protected static string $view                     = 'filament.tenant.pages.print-expense-list';

    public Collection $expenses;
    public string $generatedAt;

    public function mount(): void
    {
        $this->generatedAt = now()->format('F d, Y h:i A');

        $this->expenses = Expense::with(['category', 'payments'])
            ->orderBy('expense_date', 'desc')
            ->get();
    }
}
