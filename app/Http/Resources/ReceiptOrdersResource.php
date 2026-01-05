<?php
namespace App\Http\Resources;

use App\Enums\PaymentType;
use App\Models\Currency;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReceiptOrdersResource extends JsonResource
{
    protected static ?array $defaultCurrencyCache = null;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $defaultCurrency = $this->getDefaultCurrencyMeta();

        // Handle multiple payments or single payment
        $paymentData = $this->formatAllPayments($defaultCurrency);

        return [
            // Order Information
            'id'           => $this->id,
            'invoice_no'   => str_pad($this->invoice_no, 6, '0', STR_PAD_LEFT),
            'bill_number'  => $this->bill_number,
            'order_date'   => $this->created_at,
            'status'       => $this->status,
            'table_number' => $this->tableRoom?->name ?? null,

            // Customer Information
            'customer'     => [
                'id'      => $this->customer_id,
                'name'    => $this->customer?->name,
                'phone'   => $this->customer?->phone,
                'email'   => $this->customer?->email,
                'address' => $this->customer?->address,
            ],

            // Cashier Information
            'cashier'      => [
                'id'   => $this->cashier->id,
                'name' => $this->cashier?->name,
            ],

            // Branch Information & Receipt Configuration
            'branch'       => [
                'id'                  => $this->cashierSession->branch?->id,
                'name'                => $this->cashierSession->branch?->name,
                'address'             => $this->cashierSession->branch?->address,
                'phone'               => $this->cashierSession->branch?->phone,
                'tin'                 => $this->cashierSession->branch?->tin,
                'registration_number' => $this->cashierSession->branch?->registration_number,
                'receipt_headers'     => $this->cashierSession->branch?->receipt_headers ?? [],
                'receipt_footer'      => $this->cashierSession->branch?->receipt_footer ?? [],
                'bir_accreditation_footer' => config('sales.bir_accreditation_footer') ? explode('|', config('sales.bir_accreditation_footer')) : [],
            ],

            // Financial Information
            'totals'       => [
                'total_amount'    => $this->total_amount, // total_amount
                'tax_amount'      => $this->vat_amount,
                'discount_amount' => $this->item_discount,
                'total_due'       => $this->total_due, // gross amount
                'less_tax'        => $this->less_tax ?? 0,
                'less_discount'   => $this->less_discount ?? 0,
                'vatable_sales'   => $this->vatable_sales ?? 0,
                'vat_amount'      => $this->vat_amount ?? 0,
                'vat_exempt_sales'=> $this->vat_exempt_sales ?? 0,
                'zero_rated_sales'=> $this->zero_rated_sales ?? 0,
                'non_vat_sales'   => $this->non_vat_sales ?? 0,
                'service_charge'  => $this->service_charge ?? 0,
            ],

            // Payment Information
            'payment'      => $paymentData,

            // Order Items (using ReceiptOrderItemsResource)
            'order_items'  => new ReceiptOrderItemsResource($this->orderItems),

            // Additional metadata
            'meta'         => [
                'printed_at'   => now(),
                'receipt_type' => 'customer_receipt',
            ],
        ];
    }

    private function formatAllPayments(array $defaultCurrency)
    {
        // Check if payments relationship is loaded and has records
        if ($this->whenLoaded('payments') && $this->payments->isNotEmpty()) {
            // If multiple payments (mixed payment), return array of all payments
            if ($this->payments->count() > 1 || $this->is_mixed_payment) {
                return $this->payments->map(function ($payment) use ($defaultCurrency) {
                    return $this->formatSinglePayment($payment, $defaultCurrency);
                })->toArray();
            }
            // Single payment - return as single object (not array) for backward compatibility
            return $this->formatSinglePayment($this->payments->first(), $defaultCurrency);
        }

        // Fallback to meta_data payment info
        return $this->formatMetaPayment($defaultCurrency);
    }

    private function formatSinglePayment($payment, array $defaultCurrency): array
    {
        $paymentType = $payment->paymentMethod?->payment_type;
        $paymentTypeLabel = $paymentType instanceof PaymentType
            ? $paymentType->label()
            : $paymentType;
        $paymentDetails = (array) ($payment->payment_details ?? []);
        $paymentTypeValue = $paymentType instanceof PaymentType
            ? $paymentType->value
            : (is_string($paymentType) ? strtolower($paymentType) : null);
        $giftCheckAmount = $paymentDetails['gift_check_amount'] ?? null;
        $giftCheckAmountValue = is_numeric($giftCheckAmount) ? (float) $giftCheckAmount : null;

        // Get exchange rate from payment record or currency relationship
        $exchangeRate = (float) ($payment->exchange_rate ?? $payment->currency->exchange_rate ?? 1);
        $currencyCode = $payment->currency?->code;
        $currencySymbol = $payment->currency?->symbol;

        // If there's no currency relationship but there's an exchange rate,
        // infer currency info from payment method name or use generic info
        if (!$payment->currency && $exchangeRate > 1 && (float) $payment->amount_in_payment_currency !== (float) $payment->amount) {
            // Try to extract currency from payment method name
            $methodName = $payment->paymentMethod?->name ?? '';
            if (stripos($methodName, 'dollar') !== false || stripos($methodName, 'usd') !== false) {
                $currencyCode = 'USD';
                $currencySymbol = '$';
            } elseif (stripos($methodName, 'euro') !== false || stripos($methodName, 'eur') !== false) {
                $currencyCode = 'EUR';
                $currencySymbol = '€';
            }
        }

        return [
            'method' => $payment->paymentMethod?->name,
            'payment_type' => $paymentTypeLabel,
            'payment_type_value' => $paymentTypeValue,
            'amount_paid' => (float) $payment->amount,
            'amount_applied' => (float) ($payment->amount_applied ?? $payment->amount),
            'amount_in_payment_currency' => (float) ($payment->amount_in_payment_currency ?? $payment->amount),
            'exchange_rate' => $exchangeRate,
            'currency' => ($payment->currency || $currencyCode) ? [
                'code' => $payment->currency?->code ?? $currencyCode,
                'symbol' => $payment->currency?->symbol ?? $currencySymbol,
                'exchange_rate' => $exchangeRate,
                'is_default' => $payment->currency ? (bool) $payment->currency->is_default : false,
            ] : null,
            'base_currency' => $defaultCurrency,
            'change' => (float) $payment->change_amount,
            'customer_name' => $paymentDetails['customer_name'] ?? null,
            'customer_contact' => $paymentDetails['customer_contact'] ?? null,
            'reference_number' => $paymentDetails['reference_number']
                ?? $paymentDetails['gift_check_number']
                ?? null,
            'approval_code' => $paymentDetails['approval_code'] ?? null,
            'card_holder_name' => $paymentDetails['card_holder_name'] ?? null,
            'gift_check_number' => $paymentDetails['gift_check_number'] ?? null,
            'gift_check_amount' => $giftCheckAmountValue,
            'status' => $this->payment_status,
        ];
    }

    private function formatMetaPayment(array $defaultCurrency): ?array
    {
        $metaPayment = $this->payment_info;

        if (! $metaPayment) {
            return null;
        }

        $metaDetails = (array) ($metaPayment['payment_details'] ?? []);
        $metaPaymentTypeRaw = $metaPayment['payment_type'] ?? null;
        $metaPaymentTypeValue = is_string($metaPaymentTypeRaw)
            ? strtolower($metaPaymentTypeRaw)
            : null;
        $metaGiftAmount = $metaDetails['gift_check_amount'] ?? null;
        $metaGiftAmountValue = is_numeric($metaGiftAmount) ? (float) $metaGiftAmount : null;

        return [
            'method' => $metaPayment['method'] ?? null,
            'payment_type' => $metaPayment['payment_type'] ?? null,
            'payment_type_value' => $metaPaymentTypeValue,
            'amount_paid' => (float) ($metaPayment['amount_in_default_currency'] ?? $this->amount_tendered ?? 0),
            'amount_in_payment_currency' => (float) ($metaPayment['amount_in_payment_currency'] ?? $metaPayment['amount_in_default_currency'] ?? 0),
            'currency' => $metaPayment['currency'] ?? null,
            'base_currency' => $defaultCurrency,
            'change' => (float) ($metaPayment['change'] ?? ($this->amount_tendered - ($this->total_due + $this->service_charge))),
            'customer_name' => $metaDetails['customer_name'] ?? ($metaPayment['customer_name'] ?? null),
            'customer_contact' => $metaDetails['customer_contact'] ?? ($metaPayment['customer_contact'] ?? null),
            'reference_number' => $metaDetails['reference_number']
                ?? $metaPayment['reference_number']
                ?? $metaDetails['gift_check_number']
                ?? null,
            'approval_code' => $metaDetails['approval_code'] ?? null,
            'card_holder_name' => $metaDetails['card_holder_name'] ?? null,
            'gift_check_number' => $metaDetails['gift_check_number'] ?? null,
            'gift_check_amount' => $metaGiftAmountValue,
            'status' => $this->payment_status,
        ];
    }

    private function formatPaymentData($primaryPayment, array $defaultCurrency): ?array
    {
        if ($primaryPayment) {
            return $this->formatSinglePayment($primaryPayment, $defaultCurrency);
        }

        return $this->formatMetaPayment($defaultCurrency);
    }

    private function getDefaultCurrencyMeta(): array
    {
        if (static::$defaultCurrencyCache !== null) {
            return static::$defaultCurrencyCache;
        }

        $currency = Currency::default()->select('code', 'symbol', 'exchange_rate')->first();

        static::$defaultCurrencyCache = [
            'code' => $currency?->code ?? 'PHP',
            'symbol' => $currency?->symbol ?? '₱',
            'exchange_rate' => $currency?->exchange_rate ?? 1,
            'is_default' => true,
        ];

        return static::$defaultCurrencyCache;
    }
}
