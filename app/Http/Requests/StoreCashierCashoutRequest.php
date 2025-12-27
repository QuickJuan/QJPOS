<?php

namespace App\Http\Requests;

use App\Models\CashierCashout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCashierCashoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(CashierCashout::availableTypes())],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'source_name' => ['required', 'string', 'max:255'],
            'purpose' => ['required', 'string', 'max:255'],
            'details' => ['required', 'string', 'max:2000'],
            'meta' => ['nullable', 'array'],
        ];
    }
}
