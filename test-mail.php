<?php
// Mail Test Tool - DELETE AFTER USE
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

echo '<html><head><title>Mail Test</title><style>body{font-family:monospace;padding:20px;max-width:800px;margin:0 auto}pre{background:#f5f5f5;padding:10px;border-radius:5px;overflow-x:auto}.ok{color:green;font-weight:bold}.err{color:red;font-weight:bold}h2{border-bottom:2px solid #333;padding-bottom:5px}</style></head><body>';
echo '<h1>Mail Configuration Test</h1>';

// Show current config
echo '<h2>1. Current Mail Config</h2>';
echo '<pre>';
echo "MAIL_MAILER:    " . config('mail.default') . "\n";
echo "MAIL_FROM:      " . config('mail.from.address') . "\n";
echo "MAIL_FROM_NAME: " . config('mail.from.name') . "\n";

if (config('mail.default') === 'gmail') {
    echo "\n--- Gmail API OAuth2 ---\n";
    echo "CLIENT_ID:      " . (config('services.google.client_id') ? substr(config('services.google.client_id'), 0, 20) . '...' : 'NOT SET') . "\n";
    echo "CLIENT_SECRET:  " . (config('services.google.client_secret') ? '***SET***' : 'NOT SET') . "\n";
    echo "REFRESH_TOKEN:  " . (config('services.google.refresh_token') ? substr(config('services.google.refresh_token'), 0, 20) . '...' : 'NOT SET') . "\n";
} else {
    echo "MAIL_HOST:      " . config('mail.mailers.smtp.host') . "\n";
    echo "MAIL_PORT:      " . config('mail.mailers.smtp.port') . "\n";
    echo "MAIL_USERNAME:  " . config('mail.mailers.smtp.username') . "\n";
}
echo '</pre>';

// Test Gmail API token refresh
if (config('mail.default') === 'gmail') {
    echo '<h2>2. Gmail API Token Test</h2>';
    try {
        $gmailService = app(\App\Services\GmailOAuthService::class);
        echo '<pre>Requesting access token from Google...</pre>';
        $token = $gmailService->getAccessToken();
        if ($token) {
            echo "<p class='ok'>Access token obtained successfully! (" . strlen($token) . " chars)</p>";
        } else {
            echo "<p class='err'>Failed to get access token. Check logs.</p>";
        }
    } catch (\Exception $e) {
        echo "<p class='err'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
} else {
    // Network test for SMTP
    echo '<h2>2. Network Connectivity Test</h2>';
    $host = config('mail.mailers.smtp.host', 'localhost');
    foreach ([465, 587, 25] as $port) {
        echo "<pre>Testing $host:$port... ";
        $prefix = ($port === 465) ? 'ssl://' : '';
        $conn = @stream_socket_client("{$prefix}{$host}:{$port}", $errno, $errstr, 5);
        if ($conn) {
            echo "<span class='ok'>CONNECTED</span>";
            fclose($conn);
        } else {
            echo "<span class='err'>FAILED</span> - $errstr";
        }
        echo '</pre>';
    }
}

// Send test email
$testTo = $_GET['to'] ?? '';
if ($testTo) {
    echo '<h2>3. Sending Test Email</h2>';

    $method = $_GET['method'] ?? 'laravel';

    try {
        if ($method === 'phpmail') {
            echo '<pre>Sending via PHP mail()...</pre>';
            $subject = 'Palestine Creative Hub - Mail Test';
            $message = '<h2>Test Email</h2><p>This is a test email sent at ' . date('Y-m-d H:i:s') . '</p><p>Sent via PHP mail()</p>';
            $headers = "From: " . config('mail.from.name') . " <" . config('mail.from.address') . ">\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

            $result = mail($testTo, $subject, $message, $headers);
            echo $result
                ? "<p class='ok'>PHP mail() returned TRUE</p>"
                : "<p class='err'>PHP mail() returned FALSE</p>";
        } elseif ($method === 'gmail_direct') {
            echo '<pre>Sending via Gmail API directly...</pre>';
            $gmailService = app(\App\Services\GmailOAuthService::class);
            $result = $gmailService->sendEmail(
                $testTo,
                'Palestine Creative Hub - Gmail API Test',
                '<h2>Gmail API Test</h2><p>This email was sent directly via Gmail API at ' . date('Y-m-d H:i:s') . '</p>'
            );
            echo $result
                ? "<p class='ok'>Gmail API sent successfully!</p>"
                : "<p class='err'>Gmail API send failed. Check Laravel logs.</p>";
        } else {
            echo '<pre>Sending via Laravel Mailer (driver: ' . config('mail.default') . ')...</pre>';
            \Illuminate\Support\Facades\Mail::raw(
                'This is a test email from Palestine Creative Hub sent at ' . date('Y-m-d H:i:s'),
                function ($message) use ($testTo) {
                    $message->to($testTo)
                            ->subject('Palestine Creative Hub - Mail Test');
                }
            );
            echo "<p class='ok'>Email sent successfully!</p>";
        }
    } catch (\Exception $e) {
        echo "<p class='err'>FAILED: " . htmlspecialchars($e->getMessage()) . "</p>";
        echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
    }
}

// Form
echo '<h2>Send Test Email</h2>';
echo '<form method="get">';
echo '<label>To: <input type="email" name="to" value="' . htmlspecialchars($testTo ?: '') . '" placeholder="your@email.com" style="padding:8px;width:300px;border:1px solid #ccc;border-radius:4px"></label><br><br>';
echo '<label><input type="radio" name="method" value="laravel" checked> Laravel Mailer (' . config('mail.default') . ')</label><br>';
if (config('mail.default') === 'gmail') {
    echo '<label><input type="radio" name="method" value="gmail_direct"> Gmail API Direct</label><br>';
}
echo '<label><input type="radio" name="method" value="phpmail"> PHP mail() (sendmail)</label><br><br>';
echo '<button type="submit" style="padding:8px 20px;background:#2563eb;color:white;border:none;border-radius:4px;cursor:pointer">Send Test</button>';
echo '</form>';

echo '<hr><p style="color:red;font-weight:bold">DELETE THIS FILE AFTER USE!</p>';
echo '</body></html>';
