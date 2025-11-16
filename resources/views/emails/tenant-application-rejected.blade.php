<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(to right, #dc2626, #991b1b);
            color: white;
            padding: 30px 20px;
            border-radius: 8px 8px 0 0;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
        }
        .content {
            background: #f9fafb;
            padding: 30px 20px;
            border-radius: 0 0 8px 8px;
        }
        .section {
            margin: 20px 0;
            padding: 15px;
            background: white;
            border-radius: 6px;
            border-left: 4px solid #dc2626;
        }
        .section h2 {
            margin: 0 0 10px 0;
            color: #dc2626;
            font-size: 18px;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            padding: 20px;
            color: #666;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Application Update</h1>
    </div>

    <div class="content">
        <p>Hello <strong>{{ $ownerName }}</strong>,</p>

        <p>Thank you for your interest in StorePos. We appreciate the time you took to apply and tell us about <strong>{{ $businessName }}</strong>.</p>

        <div class="section">
            <h2>📋 Status Update</h2>
            <p>Unfortunately, we are unable to approve your application at this time.</p>

            @if($rejectionReason)
            <div style="background: #fef2f2; padding: 15px; border-radius: 4px; margin: 15px 0;">
                <p><strong>Reason for Review:</strong></p>
                <p>{{ $rejectionReason }}</p>
            </div>
            @endif
        </div>

        <div class="section">
            <h2>💡 What's Next?</h2>
            <p>This decision is not final. We evaluate applications on an ongoing basis, and circumstances may change. We encourage you to reach out to us in the future if your situation has changed, or if you'd like more information about what we're looking for.</p>
        </div>

        <div class="section">
            <h2>❓ Questions?</h2>
            <p>If you have any questions about this decision or would like to discuss your application further, please contact our team at <strong>support@storepos.online</strong>. We'd love to hear from you!</p>
        </div>
    </div>

    <div class="footer">
        <p>Best regards,<br><strong>The StorePos Team</strong></p>
        <p>© 2025 StorePos. All rights reserved.</p>
    </div>
</body>
</html>
