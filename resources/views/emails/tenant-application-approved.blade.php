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
            background: linear-gradient(to right, #22c55e, #16a34a);
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
            border-left: 4px solid #22c55e;
        }
        .section h2 {
            margin: 0 0 10px 0;
            color: #22c55e;
            font-size: 18px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e5e7eb;
            font-family: 'Courier New', monospace;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            font-weight: 600;
            color: #22c55e;
            min-width: 120px;
        }
        .info-value {
            color: #1f2937;
            word-break: break-all;
        }
        .cta-button {
            display: inline-block;
            background: #22c55e;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 6px;
            margin-top: 15px;
            font-weight: 600;
        }
        .warning-box {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 15px;
            border-radius: 4px;
            margin: 15px 0;
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
        <h1>🎉 Welcome to StorePos!</h1>
    </div>

    <div class="content">
        <p>Hello <strong>{{ $ownerName }}</strong>,</p>

        <p>Congratulations! Your application for <strong>{{ $businessName }}</strong> has been approved. We're thrilled to have you join the StorePos family!</p>

        <div class="section">
            <h2>🚀 Your Account is Ready</h2>
            <p>Your StorePos account has been set up and is ready to use. Below are your login credentials:</p>

            <div class="info-row">
                <span class="info-label">Subdomain:</span>
                <span class="info-value">{{ $subdomain }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Login URL:</span>
                <span class="info-value">{{ $loginUrl }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Email:</span>
                <span class="info-value">{{ $email }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Temporary Password:</span>
                <span class="info-value">{{ $temporaryPassword }}</span>
            </div>
        </div>

        <div class="warning-box">
            <strong>⚠️ Important:</strong> Please change your password immediately after your first login. We recommend using a strong, unique password.
        </div>

        <div class="section">
            <h2>📚 Getting Started</h2>
            <p>Here's what we recommend you do first:</p>
            <ol>
                <li>Log in to your account using the credentials above</li>
                <li>Change your temporary password to a secure password</li>
                <li>Complete your business profile</li>
                <li>Set up your first product/menu items</li>
                <li>Configure your payment methods</li>
            </ol>
        </div>

        <div class="section">
            <h2>❓ Need Help?</h2>
            <p>Our support team is here to help you get started. Feel free to reach out at <strong>support@storepos.online</strong> or visit our documentation at <strong>docs.storepos.online</strong>.</p>
        </div>

        <div style="text-align: center;">
            <a href="{{ $loginUrl }}" class="cta-button">Log In to Your Account</a>
        </div>
    </div>

    <div class="footer">
        <p>Best regards,<br><strong>The StorePos Team</strong></p>
        <p>© 2025 StorePos. All rights reserved.</p>
    </div>
</body>
</html>
