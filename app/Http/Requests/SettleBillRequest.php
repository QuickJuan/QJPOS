<?php

namespace App\Http\Requests;

use App\Models\Currency;
use App\Models\PaymentMethod;
use Illuminate\Foundation\Http\FormRequest;

class SettleBillRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'cart_id' => ['required', 'integer', 'exists:carts,id'],
            'payment_method_id' => ['required', 'integer', 'exists:payment_methods,id'],
            'currency_id' => ['nullable', 'integer', 'exists:currencies,id'],
            'amount_in_payment_currency' => ['required', 'numeric', 'min:0'],
            'amount_paid' => ['nullable', 'numeric', 'min:0'],
            'total_amount' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $paymentMethodId = $this->input('payment_method_id');
            $amountInPaymentCurrency = (float) $this->input('amount_in_payment_currency', 0);
            $totalAmount = (float) $this->input('total_amount', 0);

            if (!$paymentMethodId) {
                return;
            }

            $paymentMethod = PaymentMethod::find($paymentMethodId);

            if (! $paymentMethod) {
                return;
            }

            $currencyId = $this->input('currency_id') ?? $paymentMethod->currency_id;

            if ($paymentMethod->isCash() && ! $currencyId) {
                $validator->errors()->add('currency_id', 'Please select a currency for cash payments.');
                return;
            }

            if (! $currencyId) {
                return;
            }

            $currency = Currency::find($currencyId);

            if (! $currency) {
                $validator->errors()->add('currency_id', 'Selected currency is invalid.');
                return;
            }

            $baseAmountPaid = round($amountInPaymentCurrency * (float) $currency->exchange_rate, 2);

            if ($baseAmountPaid + 0.0001 < $totalAmount) {
                $validator->errors()->add('amount_in_payment_currency', 'Amount tendered is insufficient to settle the bill.');
            }

            $this->merge([
                'currency_id' => $currencyId,
                'computed_amount_paid' => $baseAmountPaid,
            ]);
        });
    }
}
