<?php
// Test verification email sending - DELETE AFTER USE
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

echo '<pre>';
echo "Mail driver: " . config('mail.default') . "\n";
echo "Mail from: " . config('mail.from.address') . "\n\n";

$email = $_GET['email'] ?? '';

if ($email) {
    // Find the designer
    $designer = \App\Models\Designer::where('email', $email)->first();

    if (!$designer) {
        echo "ERROR: No designer found with email: $email\n";
    } else {
        echo "Designer found: #{$designer->id} - {$designer->name}\n";
        echo "Email verified: " . ($designer->hasVerifiedEmail() ? 'YES' : 'NO') . "\n\n";

        if ($designer->hasVerifiedEmail()) {
            echo "Email is already verified. No need to send.\n";
        } else {
            echo "Sending verification email...\n";
            try {
                $designer->sendEmailVerificationNotification();
                echo "<b style='color:green'>Verification email sent successfully!</b>\n";
            } catch (\Exception $e) {
                echo "<b style='color:red'>FAILED: " . $e->getMessage() . "</b>\n";
                echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
            }
        }
    }
} else {
    // List recent unverified designers
    echo "Recent unverified designers:\n";
    $designers = \App\Models\Designer::whereNull('email_verified_at')
        ->orderByDesc('created_at')
        ->limit(10)
        ->get(['id', 'name', 'email', 'created_at']);

    foreach ($designers as $d) {
        echo "  #{$d->id} - {$d->email} - {$d->name} - {$d->created_at}\n";
    }

    echo "\nTo send verification: ?email=user@example.com\n";
}

echo '</pre>';
echo '<p style="color:red;font-weight:bold">DELETE THIS FILE AFTER USE!</p>';
