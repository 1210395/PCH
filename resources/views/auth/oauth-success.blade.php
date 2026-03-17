<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gmail OAuth2 Setup Complete</title>
    <style>
        body { font-family: system-ui, sans-serif; max-width: 700px; margin: 50px auto; padding: 20px; background: #f0fdf4; }
        .card { background: white; border-radius: 12px; padding: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        h1 { color: #16a34a; }
        .token-box { background: #f1f5f9; padding: 15px; border-radius: 8px; word-break: break-all; font-family: monospace; font-size: 14px; margin: 15px 0; border: 2px solid #e2e8f0; }
        .warning { background: #fef3c7; border-left: 4px solid #f59e0b; padding: 12px 16px; border-radius: 4px; margin: 15px 0; }
        .steps { background: #eff6ff; padding: 15px 20px; border-radius: 8px; margin: 15px 0; }
        .steps li { margin: 8px 0; }
        code { background: #e2e8f0; padding: 2px 6px; border-radius: 4px; font-size: 13px; }
    </style>
</head>
<body>
    <div class="card">
        <h1>✅ Gmail OAuth2 Authorization Successful!</h1>

        @if($refreshToken)
            <p><strong>Your Refresh Token:</strong></p>
            <div class="token-box" id="token">{{ $refreshToken }}</div>

            <button onclick="navigator.clipboard.writeText(document.getElementById('token').textContent.trim()); this.textContent='Copied!'"
                    style="background:#16a34a;color:white;border:none;padding:10px 20px;border-radius:6px;cursor:pointer;font-size:14px;">
                📋 Copy Refresh Token
            </button>

            <div class="steps">
                <p><strong>Next steps:</strong></p>
                <ol>
                    <li>Copy the refresh token above</li>
                    <li>Open your <code>.env</code> file</li>
                    <li>Set <code>GOOGLE_REFRESH_TOKEN=</code> to this value</li>
                    <li>Clear config cache</li>
                    <li>Test sending an email</li>
                </ol>
            </div>

            <div class="warning">
                ⚠️ <strong>Security:</strong> This page shows sensitive credentials. Close it after copying the token.
                Delete the OAuth setup route after configuration is complete.
            </div>
        @else
            <div class="warning">
                ⚠️ No refresh token was returned. This can happen if you've already authorized this app before.
                <br><br>
                <strong>Fix:</strong> Go to <a href="https://myaccount.google.com/permissions" target="_blank">Google Account Permissions</a>,
                remove "Palestine Creative Hub" app access, then try the authorization again.
            </div>
        @endif

        @if($accessToken)
            <p style="color:#6b7280;font-size:13px;margin-top:20px;">
                Access token received (expires in {{ $expiresIn }}s). This is temporary and will auto-refresh.
            </p>
        @endif
    </div>
</body>
</html>
