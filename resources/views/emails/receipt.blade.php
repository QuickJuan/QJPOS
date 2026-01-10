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
            background-color: #f5f5f5;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            background-color: #fff;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-radius: 8px;
            overflow: hidden;
        }
        .email-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            padding: 40px 20px;
            text-align: center;
        }
        .email-header h1 {
            margin: 0 0 10px 0;
            font-size: 28px;
        }
        .email-header p {
            margin: 0;
            opacity: 0.9;
            font-size: 14px;
        }
        .content {
            padding: 40px 30px;
            text-align: center;
        }
        .content h2 {
            color: #2c3e50;
            margin-bottom: 20px;
            font-size: 22px;
        }
        .content p {
            color: #666;
            margin-bottom: 30px;
            font-size: 15px;
            line-height: 1.8;
        }
        .receipt-info {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin: 30px 0;
            text-align: left;
        }
        .receipt-info-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e9ecef;
        }
        .receipt-info-row:last-child {
            border-bottom: none;
        }
        .receipt-info-row strong {
            color: #495057;
        }
        .receipt-info-row span {
            color: #212529;
            font-weight: 600;
        }
        .button-container {
            text-align: center;
            margin: 30px 0;
        }
        .download-button {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff !important;
            padding: 15px 40px;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 600;
            font-size: 16px;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }
        .footer {
            background: #f8f9fa;
            padding: 30px;
            text-align: center;
            font-size: 13px;
            color: #6c757d;
            border-top: 1px solid #e9ecef;
        }
        .footer p {
            margin: 5px 0;
        }
        .icon {
            font-size: 48px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="email-header">
            <h1>📧 Receipt Ready!</h1>
            <p>Your receipt is ready to view</p>
        </div>

        <div class="content">
            <div class="icon">🧾</div>
            <h2>Thank you for your purchase!</h2>
            <p>
                Your receipt has been generated and is ready for download.<br>
                Click the button below to view or download your receipt.
            </p>

            @if(isset($receiptUrl))
                <div class="receipt-info">
                    <div class="receipt-info-row">
                        <strong>Invoice Number:</strong>
                        <span>{{ $invoiceNo ?? 'N/A' }}</span>
                    </div>
                    <div class="receipt-info-row">
                        <strong>Date:</strong>
                        <span>{{ $orderDate ?? now()->format('m/d/Y h:i A') }}</span>
                    </div>
                    <div class="receipt-info-row">
                        <strong>Total Amount:</strong>
                        <span>₱{{ number_format($totalAmount ?? 0, 2) }}</span>
                    </div>
                    @if(isset($cashier))
                        <div class="receipt-info-row">
                            <strong>Served by:</strong>
                            <span>{{ $cashier }}</span>
                        </div>
                    @endif
                </div>

                <div class="button-container">
                    <a href="{{ $receiptUrl }}" class="download-button" target="_blank">
                        📄 View Receipt
                    </a>
                </div>

                <p style="font-size: 13px; color: #999;">
                    The receipt will open in a new window. You can print it directly from your browser.
                </p>
            @else
                <p style="color: #dc3545;">
                    Unable to generate receipt. Please contact support.
                </p>
            @endif
        </div>

        <div class="footer">
            <p><strong>{{ $storeName ?? 'QuickJuan POS' }}</strong></p>
            <p>{{ $storeAddress ?? '' }}</p>
            <p>{{ $storePhone ?? '' }}</p>
            <p style="margin-top: 20px; font-size: 11px;">
                This is an automated email. Please do not reply to this message.
            </p>
        </div>
    </div>
</body>
</html>
