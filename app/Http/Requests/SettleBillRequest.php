<?php

namespace App\Http\Requests;

use App\Enums\PaymentType;
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
            'customer_id' => ['nullable', 'integer'],
            'points_used' => ['nullable', 'numeric', 'min:0'],
            'amount_in_payment_currency' => ['required', 'numeric', 'min:0'],
            'amount_paid' => ['nullable', 'numeric', 'min:0'],
            'total_amount' => ['required', 'numeric', 'min:0'],
            'reference_number' => ['nullable', 'string', 'max:255'],
            'payment_details' => ['nullable', 'array'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $paymentMethodId = $this->input('payment_method_id');
            $amountInPaymentCurrency = (float) $this->input('amount_in_payment_currency', 0);
            $totalAmount = (float) $this->input('total_amount', 0);
            $paymentDetailsInput = $this->input('payment_details', []);
            $referenceNumber = $this->input('reference_number');
            $sanitize = static function ($value): string {
                if ($value === null) {
                    return '';
                }

                return is_string($value) ? trim($value) : trim((string) $value);
            };

            $paymentMethod = null;

            if (! $paymentMethodId) {
                $validator->errors()->add('payment_method_id', 'Please select a payment method.');
                return;
            }

            $paymentMethod = PaymentMethod::find($paymentMethodId);

            if (! $paymentMethod) {
                $validator->errors()->add('payment_method_id', 'Selected payment method is invalid.');
                return;
            }

            if ($paymentDetailsInput && ! is_array($paymentDetailsInput)) {
                $validator->errors()->add('payment_details', 'Payment details must be an array.');
                return;
            }

            $paymentDetails = is_array($paymentDetailsInput) ? $paymentDetailsInput : [];

            // For cash payments, use the payment method's embedded currency
            // For non-cash payments, use default currency (exchange_rate = 1)
            $exchangeRate = 1.0;

            if ($paymentMethod->isCash()) {
                // Validate that cash payment method has currency info
                if (!$paymentMethod->currency_code) {
                    $validator->errors()->add('payment_method_id', 'Selected cash payment method is missing currency information.');
                    return;
                }

                $exchangeRate = (float) ($paymentMethod->exchange_rate ?? 1.0);
            }

            $baseAmountPaid = round($amountInPaymentCurrency * $exchangeRate, 2);

            if ($baseAmountPaid + 0.0001 < $totalAmount) {
                $validator->errors()->add('amount_in_payment_currency', 'Amount tendered is insufficient to settle the bill.');
            }

            $paymentType = $paymentMethod->payment_type instanceof PaymentType
                ? $paymentMethod->payment_type->value
                : $paymentMethod->payment_type;

            switch ($paymentType) {
                case PaymentType::E_WALLET->value:
                    $reference = $sanitize($referenceNumber ?: ($paymentDetails['reference_number'] ?? ''));
                    if ($reference === '') {
                        $validator->errors()->add('reference_number', 'Reference number is required for e-wallet payments.');
                    } else {
                        $referenceNumber = $reference;
                        $paymentDetails['reference_number'] = $reference;
                    }
                    break;
                case PaymentType::CARD->value:
                    $approval = $sanitize($paymentDetails['approval_code'] ?? '');
                    $cardHolder = $sanitize($paymentDetails['card_holder_name'] ?? '');
                    if ($approval === '' || $cardHolder === '') {
                        $validator->errors()->add('payment_details', 'Approval code and cardholder name are required for card payments.');
                    } else {
                        $paymentDetails['approval_code'] = $approval;
                        $paymentDetails['card_holder_name'] = $cardHolder;
                        $referenceNumber = $approval;
                    }
                    break;
                case PaymentType::CREDIT->value:
                    $customerId = $this->input('customer_id');
                    $customerName = $sanitize($paymentDetails['customer_name'] ?? '');
                    $customerContact = $sanitize($paymentDetails['customer_contact'] ?? '');

                    if (!$customerId) {
                        $validator->errors()->add('customer_id', 'Customer ID is required for credit payments.');
                    } else {
                        // Validate customer exists in tenant database
                        if (!\DB::table('customers')->where('id', $customerId)->exists()) {
                            $validator->errors()->add('customer_id', 'The selected customer does not exist.');
                        }
                    }

                    if ($customerName === '' || $customerContact === '') {
                        $validator->errors()->add('payment_details', 'Customer name and contact information are required for credit payments.');
                    } else {
                        $paymentDetails['customer_name'] = $customerName;
                        $paymentDetails['customer_contact'] = $customerContact;
                    }
                    break;
                case PaymentType::GIFT_CHECK->value:
                    $giftCheckNumber = $sanitize($paymentDetails['gift_check_number'] ?? '');
                    $giftCheckAmount = $paymentDetails['gift_check_amount'] ?? null;
                    $giftCheckAmountValue = is_numeric($giftCheckAmount)
                        ? (float) $giftCheckAmount
                        : 0;

                    if ($giftCheckNumber === '') {
                        $validator->errors()->add('payment_details', 'Gift check number is required.');
                    }

                    if ($giftCheckAmountValue <= 0) {
                        $validator->errors()->add('payment_details', 'Gift check amount must be greater than zero.');
                    }

                    if ($giftCheckNumber !== '' && $giftCheckAmountValue > 0) {
                        $paymentDetails['gift_check_number'] = $giftCheckNumber;
                        $paymentDetails['gift_check_amount'] = $giftCheckAmountValue;
                        $referenceNumber = $giftCheckNumber;
                    }
                    break;
                case PaymentType::POINTS->value:
                    $customerId = $this->input('customer_id');
                    $pointsUsed = $this->input('points_used') ?? $totalAmount;

                    if (!$customerId) {
                        $validator->errors()->add('customer_id', 'Customer ID is required for points payment.');
                    } else {
                        // Validate customer exists and has sufficient points
                        $customer = \DB::table('customers')->where('id', $customerId)->first();
                        if (!$customer) {
                            $validator->errors()->add('customer_id', 'The selected customer does not exist.');
                        } elseif ($customer->balance < $pointsUsed) {
                            $validator->errors()->add('points_used', "Insufficient points. Customer has {$customer->balance} points but needs {$pointsUsed} points.");
                        }
                    }

                    if ($pointsUsed <= 0) {
                        $validator->errors()->add('points_used', 'Points to redeem must be greater than zero.');
                    }

                    if ($customerId && $pointsUsed > 0) {
                        $paymentDetails['points_used'] = $pointsUsed;
                        $paymentDetails['customer_id'] = $customerId;
                    }
                    break;
                default:
                    // No extra validation for other payment types
                    break;
            }

            $requiresReferenceNumber = $paymentType !== PaymentType::CASH->value
                && $paymentType !== PaymentType::CREDIT->value
                && $paymentType !== PaymentType::POINTS->value;

            $finalReferenceNumber = $sanitize($referenceNumber ?: ($paymentDetails['reference_number'] ?? ''));

            if ($requiresReferenceNumber && $finalReferenceNumber === '') {
                $validator->errors()->add('reference_number', 'Reference number is required for this payment method.');
            } else {
                $referenceNumber = $finalReferenceNumber !== '' ? $finalReferenceNumber : null;
                if ($referenceNumber !== null) {
                    $paymentDetails['reference_number'] = $referenceNumber;
                }
            }

            $this->merge([
                'computed_amount_paid' => $baseAmountPaid,
                'reference_number' => $referenceNumber,
                'payment_details' => $paymentDetails,
            ]);
        });
    }
}
