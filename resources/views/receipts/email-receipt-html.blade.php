<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt #{{ $receiptData['invoice_no'] }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Courier New', monospace;
            background: #f5f5f5;
            padding: 20px;
            line-height: 1.4;
        }

        .receipt-container {
            max-width: 400px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .receipt-header {
            text-align: center;
            border-bottom: 2px dashed #000;
            padding-bottom: 15px;
            margin-bottom: 15px;
        }

        .store-name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .branch-info {
            font-size: 12px;
            line-height: 1.5;
        }

        .receipt-info {
            margin-bottom: 15px;
            font-size: 12px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
        }

        .divider {
            border-bottom: 1px dashed #000;
            margin: 15px 0;
        }

        .items-section {
            margin-bottom: 15px;
            font-size: 12px;
        }

        .items-header {
            font-weight: bold;
            margin-bottom: 10px;
        }

        .item {
            margin-bottom: 8px;
        }

        .item-main {
            display: flex;
            justify-content: space-between;
        }

        .sub-item {
            margin-left: 20px;
            font-size: 11px;
            color: #666;
            display: flex;
            justify-content: space-between;
        }

        .totals-section {
            font-size: 12px;
            margin-bottom: 15px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }

        .grand-total {
            font-weight: bold;
            font-size: 14px;
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
            padding: 8px 0;
            margin-top: 8px;
        }

        .payment-section {
            font-size: 12px;
            margin-bottom: 15px;
        }

        .vat-section {
            font-size: 11px;
            margin-bottom: 15px;
        }

        .receipt-footer {
            text-align: center;
            font-size: 11px;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 2px dashed #000;
        }

        .print-button {
            display: block;
            width: 100%;
            padding: 12px;
            background: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 20px;
        }

        .print-button:hover {
            background: #45a049;
        }

        @media print {
            body {
                background: white;
                padding: 0;
            }

            .receipt-container {
                box-shadow: none;
                max-width: 100%;
            }

            .print-button {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        <!-- Receipt Header -->
        <div class="receipt-header">
            <div class="store-name">
                {{ $receiptData['branch']['company_name'] ?? 'Store Name' }}
            </div>
            <div class="branch-info">
                {{ $receiptData['branch']['name'] ?? '' }}<br>
                {{ $receiptData['branch']['address'] ?? '' }}<br>
                @if(!empty($receiptData['branch']['phone']))
                    {{ $receiptData['branch']['phone'] }}<br>
                @endif
                @if(!empty($receiptData['branch']['tin']))
                    VAT Reg No. {{ $receiptData['branch']['tin'] }}<br>
                @endif
                This is not an Official Receipt
            </div>
        </div>

        <!-- Receipt Info -->
        <div class="receipt-info">
            <div class="info-row">
                <span>Invoice #:</span>
                <span>{{ $receiptData['invoice_no'] }}</span>
            </div>
            <div class="info-row">
                <span>Date Time:</span>
                <span>{{ \Carbon\Carbon::parse($receiptData['order_date'])->format('m/d/Y h:i A') }}</span>
            </div>
            <div class="info-row">
                <span>Table:</span>
                <span>{{ $receiptData['table_number'] ?? 'Walk-in' }}</span>
            </div>
            <div class="info-row">
                <span>Cashier:</span>
                <span>{{ $receiptData['cashier']['name'] ?? 'N/A' }}</span>
            </div>
        </div>

        <div class="divider"></div>

        <!-- Items -->
        <div class="items-section">
            <div class="items-header">Order Items</div>
            @if(!empty($receiptData['order_items']) && is_array($receiptData['order_items']))
                @foreach($receiptData['order_items'] as $orderTypeGroup)
                    @if(isset($orderTypeGroup['orderType']))
                        <div style="margin-bottom: 10px; font-weight: bold;">
                            {{ $orderTypeGroup['orderType'] }}
                        </div>
                    @endif
                    @if(isset($orderTypeGroup['orderItems']) && is_array($orderTypeGroup['orderItems']))
                        @foreach($orderTypeGroup['orderItems'] as $item)
                            <div class="item">
                                <div class="item-main">
                                    <span>{{ $item['quantity'] ?? 0 }} pc {{ $item['description'] ?? 'Item' }}</span>
                                    <span>₱{{ number_format($item['amount'] ?? 0, 2) }}</span>
                                </div>
                                @if(!empty($item['sub_items']) && is_array($item['sub_items']))
                                    @foreach($item['sub_items'] as $subItem)
                                        <div class="sub-item">
                                            <span>&nbsp;&nbsp;• {{ $subItem['quantity'] ?? 0 }} × {{ $subItem['description'] ?? '' }}</span>
                                            @if(isset($subItem['amount']) && $subItem['amount'] > 0)
                                                <span>+₱{{ number_format($subItem['amount'], 2) }}</span>
                                            @endif
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        @endforeach
                    @endif
                @endforeach
            @else
                <div style="color: #999; font-style: italic; padding: 10px 0;">No items</div>
            @endif
        </div>

        <div class="divider"></div>

        <!-- Totals -->
        <div class="totals-section">
            <div class="total-row">
                <span>Subtotal:</span>
                <span>₱{{ number_format($receiptData['totals']['sub_total'] ?? $receiptData['totals']['total_amount'] ?? 0, 2) }}</span>
            </div>
            @if(!empty($receiptData['totals']['service_charge']) && $receiptData['totals']['service_charge'] > 0)
                <div class="total-row">
                    <span>+ Service Charge:</span>
                    <span>₱{{ number_format($receiptData['totals']['service_charge'], 2) }}</span>
                </div>
            @endif
            @if(!empty($receiptData['totals']['less_discount']) && $receiptData['totals']['less_discount'] > 0)
                <div class="total-row">
                    <span>- Discount:</span>
                    <span>₱{{ number_format($receiptData['totals']['less_discount'], 2) }}</span>
                </div>
            @endif
            <div class="total-row grand-total">
                <span>TOTAL:</span>
                <span>₱{{ number_format($receiptData['totals']['total_due'] ?? 0, 2) }}</span>
            </div>
        </div>

        <!-- Payment -->
        @if(!empty($receiptData['payment']))
            <div class="payment-section">
                @php
                    $payments = is_array($receiptData['payment']) && isset($receiptData['payment'][0])
                        ? $receiptData['payment']
                        : [$receiptData['payment']];
                @endphp
                @foreach($payments as $index => $payment)
                    @if($index > 0)
                        <div class="divider"></div>
                    @endif
                    <div class="total-row">
                        <span>Payment Method:</span>
                        <span>{{ $payment['method'] ?? 'Cash' }}</span>
                    </div>
                    <div class="total-row">
                        <span>Payment Received:</span>
                        <span>₱{{ number_format($payment['amount_paid'] ?? 0, 2) }}</span>
                    </div>
                    @if(!empty($payment['amount_in_payment_currency']))
                        <div class="total-row">
                            <span>Paid in {{ $payment['currency']['code'] ?? 'USD' }}:</span>
                            <span>{{ $payment['currency']['symbol'] ?? '$' }}{{ number_format($payment['amount_in_payment_currency'], 2) }}</span>
                        </div>
                        @if(!empty($payment['exchange_rate']))
                            <div class="total-row">
                                <span>Exchange Rate:</span>
                                <span>1 {{ $payment['currency']['code'] ?? 'USD' }} = ₱{{ number_format($payment['exchange_rate'], 2) }}</span>
                            </div>
                        @endif
                    @endif
                    @if(!empty($payment['change']) && $payment['change'] > 0)
                        <div class="total-row">
                            <span>Change:</span>
                            <span>₱{{ number_format($payment['change'], 2) }}</span>
                        </div>
                    @endif
                @endforeach
            </div>
        @endif

        <!-- VAT -->
        @if(!empty($receiptData['totals']['vatable_sales']) || !empty($receiptData['totals']['vat_amount']))
            <div class="vat-section">
                <div class="divider"></div>
                @if(!empty($receiptData['totals']['vatable_sales']) && $receiptData['totals']['vatable_sales'] > 0)
                    <div class="total-row">
                        <span>VAT Sales:</span>
                        <span>₱{{ number_format($receiptData['totals']['vatable_sales'], 2) }}</span>
                    </div>
                @endif
                @if(!empty($receiptData['totals']['vat_amount']) && $receiptData['totals']['vat_amount'] > 0)
                    <div class="total-row">
                        <span>Vatable Amount:</span>
                        <span>₱{{ number_format($receiptData['totals']['vat_amount'], 2) }}</span>
                    </div>
                @endif
            </div>
        @endif

        <!-- Footer -->
        @if(!empty($receiptData['branch']['receipt_footer']))
            <div class="receipt-footer">
                @if(is_string($receiptData['branch']['receipt_footer']))
                    {!! nl2br(e($receiptData['branch']['receipt_footer'])) !!}
                @elseif(is_array($receiptData['branch']['receipt_footer']))
                    @foreach($receiptData['branch']['receipt_footer'] as $line)
                        {{ $line }}<br>
                    @endforeach
                @endif
            </div>
        @endif

        <button class="print-button" onclick="window.print()">Print Receipt</button>
    </div>

    <script>
        // Auto-focus for better print experience
        document.addEventListener('DOMContentLoaded', function() {
            document.title = 'Receipt #{{ $receiptData['invoice_no'] }}';
        });
    </script>
</body>
</html>
