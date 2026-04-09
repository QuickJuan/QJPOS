<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt - {{ $order->invoice_no }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Monaco', 'Menlo', 'Consolas', monospace;
            background: #f3f4f6;
            padding: 20px;
        }

        .receipt-container {
            max-width: 320px;
            margin: 0 auto;
            background: white;
            padding: 16px;
            border: 2px solid #d1d5db;
            border-radius: 8px;
            font-size: 13px;
            line-height: 1.4;
        }

        /* Header */
        .receipt-header {
            text-align: center;
            margin-bottom: 16px;
        }

        .store-name {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 4px;
        }

        .branch-info {
            font-size: 12px;
            color: #666;
            line-height: 1.3;
        }

        .receipt-header-lines {
            font-size: 11px;
            color: #666;
            margin-top: 8px;
        }

        .divider {
            border-bottom: 1px dashed #d1d5db;
            margin: 12px 0;
        }

        /* Receipt Info */
        .receipt-info {
            margin-bottom: 12px;
            font-size: 12px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 4px;
        }

        .info-label {
            font-weight: normal;
        }

        .info-value {
            text-align: right;
        }

        /* Items Section */
        .items-section {
            margin: 12px 0;
        }

        .items-header {
            text-align: center;
            font-weight: bold;
            margin-bottom: 8px;
            font-size: 12px;
        }

        .order-type-group {
            margin-bottom: 12px;
        }

        .order-type-header {
            text-align: center;
            font-weight: bold;
            font-size: 12px;
            margin-bottom: 6px;
            padding-bottom: 4px;
            border-bottom: 1px dashed #d1d5db;
        }

        .item {
            margin-bottom: 8px;
        }

        .item-main {
            display: flex;
            justify-content: space-between;
            gap: 8px;
        }

        .item-name {
            flex: 1;
            font-weight: 500;
        }

        .item-amount {
            text-align: right;
            font-weight: 500;
            white-space: nowrap;
        }

        .item-qty-price {
            margin-left: 12px;
            font-size: 11px;
            color: #666;
        }

        .item-children {
            margin-left: 12px;
            margin-top: 4px;
            border-left: 2px solid #e5e7eb;
            padding-left: 8px;
        }

        .child-item {
            font-size: 11px;
            color: #666;
            display: flex;
            justify-content: space-between;
            gap: 8px;
            margin-bottom: 2px;
        }

        .child-name {
            flex: 1;
        }

        .child-amount {
            text-align: right;
            white-space: nowrap;
        }

        .item-tax {
            font-size: 11px;
            color: #666;
            margin-left: 12px;
            display: flex;
            justify-content: space-between;
            gap: 8px;
        }

        .item-discount {
            font-size: 11px;
            color: #666;
            margin-left: 12px;
            display: flex;
            justify-content: space-between;
            gap: 8px;
        }

        /* Totals Section */
        .totals-section {
            margin-top: 12px;
        }

        .totals-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 6px;
            font-size: 12px;
        }

        .totals-label {
            font-weight: normal;
        }

        .totals-value {
            text-align: right;
            white-space: nowrap;
        }

        .grand-total {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid #000;
            font-size: 14px;
            font-weight: bold;
        }

        .grand-total-label {
            font-weight: bold;
        }

        .grand-total-value {
            text-align: right;
            white-space: nowrap;
        }

        /* Tax Info */
        .tax-info {
            font-size: 11px;
            color: #666;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px dashed #d1d5db;
        }

        .tax-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 4px;
        }

        /* Payment Info */
        .payment-info {
            font-size: 11px;
            color: #666;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px dashed #d1d5db;
        }

        .payment-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 4px;
        }

        /* Footer */
        .receipt-footer {
            text-align: center;
            margin-top: 12px;
            padding-top: 12px;
            border-top: 1px dashed #d1d5db;
            font-size: 11px;
            color: #666;
            line-height: 1.4;
        }

        .footer-message {
            margin-bottom: 4px;
        }

        @media print {
            body {
                padding: 0;
                background: white;
            }
            .receipt-container {
                max-width: 100%;
                margin: 0;
                border: none;
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
    <div class="receipt-container" id="receipt-content">
        @php
            $generalSettings = app(\App\Settings\GeneralSettings::class);
            $showFeedbackQr = (bool) ($generalSettings->enable_feedback_qr_code ?? false);
            $feedbackQr = null;

            if ($showFeedbackQr && $order->invoice_no) {
                $feedbackUrl = route('customer-feedback.create', ['invoiceNo' => $order->invoice_no]);
                $feedbackQr = \App\Services\QrCodeService::pngDataUri($feedbackUrl, 140, 2);
            }
        @endphp

        <!-- Header -->
        <div class="receipt-header">
            @if($order->branch?->logo_url)
                <div style="margin-bottom: 8px;">
                    <img src="{{ $order->branch->logo_url }}" alt="Logo" style="max-width: 50px; height: auto;">
                </div>
            @endif

            <!-- Company/Store Name (from settings or branch) -->
            <div class="store-name">
                {{ $companyName ?? ($order->branch?->name ?? config('app.name', 'Quick Juan')) }}
            </div>

            <!-- Branch Info (address and phone) -->
            @if($order->branch?->address || $order->branch?->phone)
                <div class="branch-info">
                    @if($order->branch?->address)
                        {{ $order->branch->address }}<br>
                    @endif
                    @if($order->branch?->phone)
                        {{ $order->branch->phone }}
                    @endif
                </div>
            @endif

            <!-- Receipt Headers (VAT info, registration, etc.) -->
            @if($order->branch?->receipt_headers && is_array($order->branch->receipt_headers))
                <div class="receipt-header-lines">
                    @foreach($order->branch->receipt_headers as $line)
                        <div>{{ $line }}</div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="divider"></div>

        <!-- Receipt Info -->
        <div class="receipt-info">
            <div class="info-row">
                <span class="info-label">Invoice #:</span>
                <span class="info-value">{{ $order->invoice_no }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Date Time:</span>
                <span class="info-value">{{ $order->created_at?->format('M d, Y H:i A') ?? 'N/A' }}</span>
            </div>
            @if($order->table_number)
                <div class="info-row">
                    <span class="info-label">Table:</span>
                    <span class="info-value">{{ $order->table_number }}</span>
                </div>
            @endif
            @if($order->cashier?->name)
                <div class="info-row">
                    <span class="info-label">Cashier:</span>
                    <span class="info-value">{{ $order->cashier->name }}</span>
                </div>
            @endif
        </div>

        <div class="divider"></div>

        <!-- Items Section -->
        <div class="items-section">
            <div class="items-header">ORDER ITEMS</div>

            @php
                $groupedItems = $order->orderItems->groupBy('order_type');
            @endphp

            @foreach($groupedItems as $orderType => $items)
                <div class="order-type-group">
                    <div class="order-type-header">
                        @switch($orderType)
                            @case('dine-in')
                                DINE IN
                                @break
                            @case('takeout')
                                TAKEOUT
                                @break
                            @case('delivery')
                                DELIVERY
                                @break
                            @default
                                {{ strtoupper(str_replace('-', ' ', $orderType)) }}
                        @endswitch
                    </div>

                    @foreach($items->where('parent_id', null) as $item)
                        <div class="item">
                            <div class="item-main">
                                <span class="item-name">
                                    {{ $item->quantity > 1 ? $item->quantity . 'x ' : '' }}{{ $item->product?->name ?? $item->name ?? 'Item' }}
                                </span>
                                <span class="item-amount">₱{{ number_format($item->amount ?? 0, 2) }}</span>
                            </div>

                            @if($item->quantity > 1 && $item->amount)
                                <div class="item-qty-price">{{ $item->quantity }} × ₱{{ number_format(($item->amount ?? 0) / $item->quantity, 2) }}</div>
                            @endif

                            <!-- Child Items (Modifiers/Sub-items) -->
                            @php
                                $childItems = $order->orderItems->where('parent_id', $item->id);
                            @endphp

                            @if($childItems->count() > 0)
                                <div class="item-children">
                                    @foreach($childItems as $child)
                                        <div class="child-item">
                                            <span class="child-name">
                                                {{ $child->quantity > 1 ? $child->quantity . 'x ' : '' }}{{ $child->product?->name ?? $child->name ?? 'Item' }}
                                            </span>
                                            @if($child->amount > 0)
                                                <span class="child-amount">+₱{{ number_format($child->amount ?? 0, 2) }}</span>
                                            @else
                                                <span class="child-amount">Included</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            <!-- Less Tax -->
                            @if($item->less_tax > 0)
                                <div class="item-tax">
                                    <span>Less Tax:</span>
                                    <span>-₱{{ number_format($item->less_tax ?? 0, 2) }}</span>
                                </div>
                            @endif

                            <!-- Discount -->
                            @if($item->discount_amount > 0)
                                <div class="item-discount">
                                    <span>Less Discount:</span>
                                    <span>-₱{{ number_format($item->discount_amount ?? 0, 2) }}</span>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>

        <div class="divider"></div>

        <!-- Totals -->
        <div class="totals-section">
            @php
                $subtotal = $order->subtotal ?? 0;
                $discount = $order->discount_amount ?? $order->total_discount ?? 0;
                $tax = $order->vat_amount ?? $order->less_tax ?? 0;
                $total = $order->total_amount ?? 0;
            @endphp

            <div class="totals-row">
                <span class="totals-label">Subtotal:</span>
                <span class="totals-value">₱{{ number_format($subtotal, 2) }}</span>
            </div>

            @if($discount > 0)
                <div class="totals-row">
                    <span class="totals-label">Discount:</span>
                    <span class="totals-value">-₱{{ number_format($discount, 2) }}</span>
                </div>
            @endif

            @if($tax > 0)
                <div class="totals-row">
                    <span class="totals-label">Tax (VAT):</span>
                    <span class="totals-value">₱{{ number_format($tax, 2) }}</span>
                </div>
            @endif

            <div class="grand-total">
                <span class="grand-total-label">TOTAL:</span>
                <span class="grand-total-value">₱{{ number_format($total, 2) }}</span>
            </div>
        </div>

        <!-- Tax Info -->
        @php
            // Calculate tax totals from items
            $vatableSales = $order->orderItems->sum('vatable_sales') ?? 0;
            $vatAmount = $order->orderItems->sum('vat_amount') ?? 0;
            $vatExemptSales = $order->orderItems->sum('vat_exempt_sales') ?? 0;
            $nonVatSales = $order->orderItems->sum('non_vat_sales') ?? 0;
        @endphp

        @if($vatableSales > 0 || $vatAmount > 0 || $vatExemptSales > 0 || $nonVatSales > 0)
            <div class="tax-info">
                @if($vatableSales > 0)
                    <div class="tax-row">
                        <span>VAT Sales:</span>
                        <span>₱{{ number_format($vatableSales, 2) }}</span>
                    </div>
                @endif

                @if($vatAmount > 0)
                    <div class="tax-row">
                        <span>Vatable Amount:</span>
                        <span>₱{{ number_format($vatAmount, 2) }}</span>
                    </div>
                @endif

                @if($vatExemptSales > 0)
                    <div class="tax-row">
                        <span>VAT Exempt Sales:</span>
                        <span>₱{{ number_format($vatExemptSales, 2) }}</span>
                    </div>
                @endif

                @if($nonVatSales > 0)
                    <div class="tax-row">
                        <span>Non-VAT Sales:</span>
                        <span>₱{{ number_format($nonVatSales, 2) }}</span>
                    </div>
                @endif
            </div>
        @endif

        <!-- Payment Info -->
        @php
            // Use $paymentData if provided (from controller), otherwise fall back to $order->payment
            $paymentInfo = $paymentData ?? $order->payment;
        @endphp
        @if($paymentInfo)
            @php
                // Determine if this is a mixed payment (array of payments) or single payment
                $isMixedPayment = is_array($paymentInfo) && isset($paymentInfo[0]);
                $payments = $isMixedPayment ? $paymentInfo : [$paymentInfo];
                $baseCurrency = $paymentInfo['base_currency'] ?? ($isMixedPayment && isset($payments[0]['base_currency']) ? $payments[0]['base_currency'] : ['code' => 'PHP', 'symbol' => '₱']);
            @endphp

            <div class="payment-info">
                @if($isMixedPayment && count($payments) > 1)
                    <div style="text-align: center; font-weight: bold; margin-bottom: 8px; padding-bottom: 6px; border-bottom: 1px solid #333;">PAYMENT</div>
                    @php
                        $totalPaid = 0;
                        $totalChange = 0;
                    @endphp
                    @foreach($payments as $index => $payment)
                        <div style="margin-bottom: 12px; padding-bottom: 8px; @if($index < count($payments) - 1) border-bottom: 1px dashed #666; @endif">
                            <div style="font-weight: 600; font-size: 11px; margin-bottom: 4px;">Payment {{ $index + 1 }}</div>

                            @if($payment['method'] ?? false)
                                <div class="payment-row">
                                    <span>Payment Method:</span>
                                    <span>{{ $payment['method'] }}</span>
                                </div>
                            @endif

                            @php
                                // Use amount_in_payment_currency if available, otherwise use amount_applied/amount_paid
                                $hasPaymentCurrency = ($payment['amount_in_payment_currency'] ?? 0) > 0;
                                $exchangeRate = $payment['currency']['exchange_rate'] ?? 1;

                                if ($hasPaymentCurrency) {
                                    $displayAmount = $payment['amount_in_payment_currency'];
                                    $displaySymbol = $payment['currency']['symbol'] ?? '';
                                    $displayCurrency = $payment['currency']['code'] ?? '';
                                } else {
                                    $displayAmount = $payment['amount_applied'] ?? $payment['amount_paid'] ?? 0;
                                    $displaySymbol = $baseCurrency['symbol'] ?? '₱';
                                    $displayCurrency = '';
                                }
                            @endphp

                            <div class="payment-row">
                                <span>Amount:</span>
                                <span>{{ $displaySymbol }}{{ number_format($displayAmount, 2) }}</span>
                            </div>

                            @if($hasPaymentCurrency && $exchangeRate > 1)
                                <div class="payment-row" style="font-size: 10px; color: #666;">
                                    <span>Exchange Rate:</span>
                                    <span>1 {{ $payment['currency']['code'] }} = {{ $baseCurrency['symbol'] ?? '₱' }}{{ number_format($exchangeRate, 2) }}</span>
                                </div>
                                <div class="payment-row" style="font-size: 10px; color: #666;">
                                    <span>Value in {{ $baseCurrency['code'] }}:</span>
                                    <span>{{ $baseCurrency['symbol'] ?? '₱' }}{{ number_format($payment['amount_applied'] ?? $payment['amount_paid'] ?? 0, 2) }}</span>
                                </div>
                            @endif

                            @php
                                $totalPaid += $payment['amount_applied'] ?? $payment['amount_paid'] ?? 0;
                            @endphp

                            @if(($payment['change_amount'] ?? $payment['change'] ?? 0) > 0)
                                <div class="payment-row">
                                    <span>Change:</span>
                                    <span>{{ $baseCurrency['symbol'] ?? '₱' }}{{ number_format($payment['change_amount'] ?? $payment['change'] ?? 0, 2) }}</span>
                                </div>
                                @php
                                    $totalChange += $payment['change_amount'] ?? $payment['change'] ?? 0;
                                @endphp
                            @endif

                            @if(isset($payment['customer_name']))
                                <div class="payment-row" style="font-size: 10px;">
                                    <span>Customer:</span>
                                    <span>{{ $payment['customer_name'] }}</span>
                                </div>
                            @endif

                            @if(isset($payment['reference_number']))
                                <div class="payment-row" style="font-size: 10px;">
                                    <span>Reference No.:</span>
                                    <span>{{ $payment['reference_number'] }}</span>
                                </div>
                            @endif

                            @if(isset($payment['approval_code']))
                                <div class="payment-row" style="font-size: 10px;">
                                    <span>Approval Code:</span>
                                    <span>{{ $payment['approval_code'] }}</span>
                                </div>
                            @endif

                            @if(isset($payment['card_holder_name']))
                                <div class="payment-row" style="font-size: 10px;">
                                    <span>Cardholder:</span>
                                    <span>{{ $payment['card_holder_name'] }}</span>
                                </div>
                            @endif
                        </div>
                    @endforeach

                    <div class="payment-row" style="font-weight: bold; padding-top: 8px; border-top: 1px solid #333;">
                        <span>Total Paid:</span>
                        <span>{{ $baseCurrency['symbol'] ?? '₱' }}{{ number_format($totalPaid, 2) }}</span>
                    </div>
                    @if($totalChange > 0)
                        <div class="payment-row" style="font-weight: bold;">
                            <span>Total Change:</span>
                            <span>{{ $baseCurrency['symbol'] ?? '₱' }}{{ number_format($totalChange, 2) }}</span>
                        </div>
                    @endif
                @else
                    @php
                        $payment = $payments[0];
                        $hasPaymentCurrency = ($payment['amount_in_payment_currency'] ?? 0) > 0;
                        $exchangeRate = $payment['currency']['exchange_rate'] ?? 1;

                        // Use amount_in_payment_currency if available, otherwise use amount_paid
                        if ($hasPaymentCurrency) {
                            $displayAmount = $payment['amount_in_payment_currency'];
                            $displaySymbol = $payment['currency']['symbol'] ?? '';
                            $displayCurrency = $payment['currency']['code'] ?? '';
                        } else {
                            $displayAmount = $payment['amount_paid'] ?? 0;
                            $displaySymbol = $baseCurrency['symbol'] ?? '₱';
                            $displayCurrency = '';
                        }
                    @endphp

                    <div class="payment-row">
                        <span>Amount Paid:</span>
                        <span>{{ $displaySymbol }}{{ number_format($displayAmount, 2) }}</span>
                    </div>

                    @if($hasPaymentCurrency && $exchangeRate > 1)
                        <div class="payment-row" style="font-size: 10px; color: #666;">
                            <span>Exchange Rate:</span>
                            <span>1 {{ $payment['currency']['code'] }} = {{ $baseCurrency['symbol'] ?? '₱' }}{{ number_format($exchangeRate, 2) }}</span>
                        </div>
                        <div class="payment-row" style="font-size: 10px; color: #666;">
                            <span>Value in {{ $baseCurrency['code'] }}:</span>
                            <span>{{ $baseCurrency['symbol'] ?? '₱' }}{{ number_format($payment['amount_paid'] ?? 0, 2) }}</span>
                        </div>
                    @endif

                    @if(($payment['change'] ?? 0) >= 0)
                        <div class="payment-row">
                            <span>Change:</span>
                            <span>{{ $baseCurrency['symbol'] ?? '₱' }}{{ number_format($payment['change'] ?? 0, 2) }}</span>
                        </div>
                    @endif
                    @if($payment['method'] ?? false)
                        <div class="payment-row">
                            <span>Payment Method:</span>
                            <span>{{ $payment['method'] }}</span>
                        </div>
                    @endif

                    @if(isset($payment['customer_name']))
                        <div class="payment-row" style="font-size: 10px;">
                            <span>Customer:</span>
                            <span>{{ $payment['customer_name'] }}</span>
                        </div>
                    @endif

                    @if(isset($payment['reference_number']))
                        <div class="payment-row" style="font-size: 10px;">
                            <span>Reference No.:</span>
                            <span>{{ $payment['reference_number'] }}</span>
                        </div>
                    @endif

                    @if(isset($payment['approval_code']))
                        <div class="payment-row" style="font-size: 10px;">
                            <span>Approval Code:</span>
                            <span>{{ $payment['approval_code'] }}</span>
                        </div>
                    @endif

                    @if(isset($payment['card_holder_name']))
                        <div class="payment-row" style="font-size: 10px;">
                            <span>Cardholder:</span>
                            <span>{{ $payment['card_holder_name'] }}</span>
                        </div>
                    @endif
                @endif
            </div>
        @endif

        @if($feedbackQr)
            <div style="text-align: center; margin-top: 12px; padding-top: 12px; border-top: 1px dashed #d1d5db;">
                <div style="font-size: 11px; font-weight: 600; color: #111;">Tell us how we did</div>
                <div style="margin-top: 8px;">
                    <img src="{{ $feedbackQr }}" alt="Feedback QR Code" style="width: 120px; height: 120px;">
                </div>
                <div style="margin-top: 6px; font-size: 10px; color: #666;">
                    Scan to leave feedback for Invoice #{{ $order->invoice_no }}
                </div>
            </div>
        @endif

        <!-- Footer -->
        <div class="receipt-footer">
            @if($order->branch?->receipt_footer)
                @if(is_array($order->branch->receipt_footer))
                    @foreach($order->branch->receipt_footer as $line)
                        <div>{{ $line }}</div>
                    @endforeach
                @else
                    <div>{{ $order->branch->receipt_footer }}</div>
                @endif
            @else
                <div class="footer-message">Thank you for your business!</div>
            @endif
        </div>
    </div>
</body>
</html>
