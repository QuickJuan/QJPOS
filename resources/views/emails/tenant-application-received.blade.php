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
            background: linear-gradient(to right, #4f46e5, #7c3aed);
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
            border-left: 4px solid #4f46e5;
        }
        .section h2 {
            margin: 0 0 10px 0;
            color: #4f46e5;
            font-size: 18px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            font-weight: 600;
            color: #4f46e5;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            padding: 20px;
            color: #666;
            font-size: 14px;
        }
        .cta-button {
            display: inline-block;
            background: #4f46e5;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 6px;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🎉 Application Received!</h1>
    </div>

    <div class="content">
        <p>Hello <strong>{{ $ownerName }}</strong>,</p>

        <p>Thank you for applying to <strong>StorePos</strong>! We're excited to help <strong>{{ $businessName }}</strong> succeed with our point-of-sale system.</p>

        <div class="section">
            <h2>📋 Application Details</h2>
            <div class="info-row">
                <span class="info-label">Business Name:</span>
                <span>{{ $application->business_name }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Owner Name:</span>
                <span>{{ $application->owner_name }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Email:</span>
                <span>{{ $application->owner_email }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Phone:</span>
                <span>{{ $application->owner_phone }}</span>
            </div>
        </div>

        <div class="section">
            <h2>⏱️ What Happens Next?</h2>
            <p>Our team will review your application carefully and get back to you within <strong>2-3 business days</strong> with next steps. We'll contact you using the email address and phone number you provided.</p>
            <p>If you have any questions in the meantime, please don't hesitate to reach out!</p>
        </div>

        <div class="section">
            <h2>❓ Questions?</h2>
            <p>If you need to provide additional information or have any questions about your application, please reply to this email or contact our support team at <strong>support@storepos.online</strong>.</p>
        </div>
    </div>

    <div class="footer">
        <p>Best regards,<br><strong>The StorePos Team</strong></p>
        <p>© 2025 StorePos. All rights reserved.</p>
    </div>
</body>
</html>
