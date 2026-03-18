<?php

namespace App\Filament\Tenant\Resources\ExpenseResource\Pages;

use App\Enums\ExpensePaymentMethod;
use App\Filament\Tenant\Resources\ExpenseResource;
use App\Models\ExpensePayment;
use Filament\Resources\Pages\CreateRecord;

class CreateExpense extends CreateRecord
{
    protected static string $resource = ExpenseResource::class;

    /** Temporarily hold virtual form fields that aren't persisted. */
    protected array $paymentData = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Extract the virtual payment fields from form data
        $this->paymentData = [
            'reference_number' => $data['reference_number']   ?? null,
            'payment_date'     => $data['payment_date']       ?? ($data['expense_date'] ?? today()->toDateString()),
            'payment_remarks'  => $data['payment_remarks']    ?? null,
            'payment_schedule' => $data['payment_schedule']   ?? [],
        ];

        // Remove virtual keys — they have no DB columns
        unset(
            $data['reference_number'],
            $data['payment_date'],
            $data['payment_remarks'],
            $data['payment_schedule'],
        );

        // Set initial status
        $data['status']      = $data['payment_method'] === 'purchase' ? 'unpaid' : 'paid';
        $data['recorded_by'] = auth()->id();

        return $data;
    }

    protected function afterCreate(): void
    {
        $record = $this->record;

        if (in_array($record->payment_method->value, ['cash', 'bank'])) {
            // Auto-create a single paid payment record
            ExpensePayment::create([
                'expense_id'       => $record->id,
                'due_date'         => $this->paymentData['payment_date'] ?? $record->expense_date->toDateString(),
                'amount'           => $record->amount,
                'is_paid'          => true,
                'paid_date'        => $this->paymentData['payment_date'] ?? $record->expense_date->toDateString(),
                'reference_number' => $this->paymentData['reference_number'] ?? null,
                'remarks'          => $this->paymentData['payment_remarks'] ?? null,
                'paid_by'          => auth()->id(),
            ]);
        } elseif ($record->payment_method === ExpensePaymentMethod::Purchase) {
            foreach ($this->paymentData['payment_schedule'] as $installment) {
                ExpensePayment::create([
                    'expense_id' => $record->id,
                    'due_date'   => $installment['due_date'],
                    'amount'     => $installment['amount'],
                    'is_paid'    => false,
                    'remarks'    => $installment['notes'] ?? null,
                ]);
            }
            $record->refresh();
            $record->updateStatus();
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
