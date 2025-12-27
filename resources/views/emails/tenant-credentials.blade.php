<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tenant Account Credentials</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Oxygen', 'Ubuntu', 'Cantarell', sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: bold;
        }
        .content {
            padding: 30px;
        }
        .greeting {
            margin-bottom: 20px;
        }
        .credentials-box {
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 6px;
            padding: 20px;
            margin: 20px 0;
        }
        .credentials-box .label {
            font-weight: 600;
            color: #667eea;
            margin-bottom: 5px;
            font-size: 12px;
            text-transform: uppercase;
        }
        .credentials-box .value {
            font-family: 'Courier New', monospace;
            font-size: 14px;
            word-break: break-all;
            margin-bottom: 15px;
        }
        .login-button {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            margin: 20px 0;
        }
        .warning {
            background-color: #fff3cd;
            border: 1px solid #ffc107;
            color: #856404;
            padding: 12px;
            border-radius: 4px;
            margin: 20px 0;
            font-size: 13px;
        }
        .footer {
            background-color: #f9f9f9;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #666;
            border-top: 1px solid #ddd;
        }
        .divider {
            margin: 15px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ $tenant_name }}</h1>
            <p>{{ isset($user_type) ? ucfirst($user_type) . ' ' : '' }}Account Setup Complete</p>
        </div>

        <div class="content">
            <div class="greeting">
                <p>Hello,</p>
                <p>Your {{ isset($user_type) ? strtolower($user_type) . ' ' : '' }}account for <strong>{{ $tenant_name }}</strong> has been successfully created! Here are your login credentials:</p>
            </div>

            <div class="credentials-box">
                <div class="label">Email Address</div>
                <div class="value">{{ $email }}</div>

                <div class="label">Password</div>
                <div class="value">{{ $password }}</div>
            </div>

            <div class="warning">
                <strong>⚠️ Important:</strong> We recommend changing your password immediately upon first login for security purposes.
            </div>

            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ $login_url }}" class="login-button">Login to Your Account</a>
            </div>

            <div class="divider">
                <p><strong>Getting Started:</strong></p>
                <ul>
                    <li>Visit the login page using the button above or your domain</li>
                    <li>Enter your email and password</li>
                    <li>Select your branch location</li>
                    <li>Change your password in account settings</li>
                </ul>
            </div>

            <p>If you have any questions or need assistance, please don't hesitate to reach out to our support team.</p>

            <p style="margin-top: 40px;">Best regards,<br><strong>{{ config('app.name') }} Team</strong></p>
        </div>

        <div class="footer">
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
            <p>This email was sent because a new tenant account was created. If you did not request this, please contact support.</p>
        </div>
    </div>
</body>
</html>
