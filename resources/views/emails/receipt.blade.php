<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #2c3e50;
            color: #fff;
            padding: 30px 20px;
            text-align: center;
            border-radius: 4px 4px 0 0;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
        }
        .content {
            background-color: #fff;
            padding: 30px 20px;
            border-radius: 0 0 4px 4px;
            border: 1px solid #e0e0e0;
        }
        .summary-box {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 4px;
            margin: 20px 0;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        .summary-row:last-child {
            border-bottom: none;
        }
        .summary-label {
            font-weight: 600;
            color: #2c3e50;
        }
        .summary-value {
            text-align: right;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 15px 0;
            border-top: 2px solid #2c3e50;
            margin-top: 10px;
            font-size: 18px;
            font-weight: bold;
        }
        .download-button {
            display: inline-block;
            background-color: #2196F3;
            color: #fff;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 4px;
            font-weight: 600;
            margin: 20px 0;
            text-align: center;
        }
        .download-button:hover {
            background-color: #1976D2;
        }
        .button-container {
            text-align: center;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            padding: 20px;
            color: #666;
            font-size: 12px;
            border-top: 1px solid #eee;
            margin-top: 20px;
        }
        .note {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 12px;
            margin: 20px 0;
            border-radius: 2px;
            font-size: 13px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Receipt Confirmation</h1>
        </div>

        <div class="content">
            <p>Dear Customer,</p>
            <p>Thank you for your order! Your receipt details are below.</p>

            <div class="summary-box">
                <div class="summary-row">
                    <span class="summary-label">Invoice #:</span>
                    <span class="summary-value"><strong>{{ $order->invoice_no }}</strong></span>
                </div>
                <div class="summary-row">
                    <span class="summary-label">Date:</span>
                    <span class="summary-value">{{ $order->created_at ? $order->created_at->format('M d, Y H:i A') : 'N/A' }}</span>
                </div>
                <div class="summary-row">
                    <span class="summary-label">Branch:</span>
                    <span class="summary-value">{{ $order->branch?->name ?? 'Main Branch' }}</span>
                </div>
                <div class="summary-row">
                    <span class="summary-label">Table:</span>
                    <span class="summary-value">{{ $order->table_number ?? 'Walk-in' }}</span>
                </div>
                <div class="summary-row">
                    <span class="summary-label">Cashier:</span>
                    <span class="summary-value">{{ $order->cashier?->name ?? 'N/A' }}</span>
                </div>

                <div class="total-row">
                    <span>Total Amount:</span>
                    <span>₱{{ number_format($order->total_amount ?? 0, 2) }}</span>
                </div>
            </div>

            <div class="button-container">
                <a href="{{ route('transactions.download-receipt', ['order' => $order->id]) }}" class="download-button">📥 Download Receipt</a>
            </div>

            <div class="note">
                <strong>💡 Tip:</strong> Click the button above to download your detailed receipt with all itemized details.
            </div>

            <div class="footer">
                <p>Thank you for choosing us! We appreciate your business.</p>
                <p>If you have any questions, please contact us.</p>
            </div>
        </div>
    </div>
</body>
</html>
