<?php

namespace App\Filament\Tenant\Pages;

use App\Models\Expense;
use Filament\Pages\Page;

class PrintExpense extends Page
{
    protected static bool   $shouldRegisterNavigation = false;
    protected static string $view                     = 'filament.tenant.pages.print-expense';

    public Expense $expense;
    public string $generatedAt;

    public function mount(): void
    {
        $id = request()->integer('record');
        abort_unless($id, 404);

        $this->expense = Expense::with([
            'category',
            'payments.paidBy',
            'recordedBy',
            'media',
        ])->findOrFail($id);

        $this->generatedAt = now()->format('F d, Y h:i A');
    }
}
