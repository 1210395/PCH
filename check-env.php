<?php
// Check what .env the server is actually reading - DELETE AFTER USE
echo '<pre>';

// 1. Read the actual .env file
$envPath = __DIR__ . '/.env';
echo "=== .env file location ===\n";
echo "Path: $envPath\n";
echo "Exists: " . (file_exists($envPath) ? 'YES' : 'NO') . "\n";
echo "Last modified: " . date('Y-m-d H:i:s', filemtime($envPath)) . "\n";
echo "Size: " . filesize($envPath) . " bytes\n\n";

// 2. Show the MAIL_ lines from the actual file
echo "=== MAIL_ lines in .env file ===\n";
$lines = file($envPath);
foreach ($lines as $line) {
    if (stripos($line, 'MAIL_') === 0) {
        echo $line;
    }
}

// 3. Check for cached config
echo "\n=== Cached config ===\n";
$cachedPath = __DIR__ . '/bootstrap/cache/config.php';
echo "Config cache exists: " . (file_exists($cachedPath) ? 'YES (' . date('Y-m-d H:i:s', filemtime($cachedPath)) . ')' : 'NO') . "\n";

if (file_exists($cachedPath)) {
    $cached = require $cachedPath;
    echo "Cached mail.default: " . ($cached['mail']['default'] ?? 'not set') . "\n";
    echo "Cached mail.mailers.smtp.host: " . ($cached['mail']['mailers']['smtp']['host'] ?? 'not set') . "\n";
    echo "Cached mail.mailers.smtp.username: " . ($cached['mail']['mailers']['smtp']['username'] ?? 'not set') . "\n";
    echo "Cached mail.from.address: " . ($cached['mail']['from']['address'] ?? 'not set') . "\n";
}

// 4. Delete cache and show fresh values
echo "\n=== Deleting cache and reading fresh ===\n";
if (file_exists($cachedPath)) {
    unlink($cachedPath);
    echo "Cache file DELETED\n";
}

// Re-bootstrap to read fresh .env
echo "\n=== Fresh config (after cache delete) ===\n";

// Clear OPcache if available
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "OPcache cleared\n";
}

// Read .env manually with Dotenv
$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

echo "Fresh MAIL_MAILER: " . ($_ENV['MAIL_MAILER'] ?? $_SERVER['MAIL_MAILER'] ?? getenv('MAIL_MAILER') ?: 'NOT FOUND') . "\n";
echo "Fresh MAIL_HOST: " . ($_ENV['MAIL_HOST'] ?? $_SERVER['MAIL_HOST'] ?? getenv('MAIL_HOST') ?: 'NOT FOUND') . "\n";
echo "Fresh MAIL_USERNAME: " . ($_ENV['MAIL_USERNAME'] ?? $_SERVER['MAIL_USERNAME'] ?? getenv('MAIL_USERNAME') ?: 'NOT FOUND') . "\n";
echo "Fresh MAIL_FROM_ADDRESS: " . ($_ENV['MAIL_FROM_ADDRESS'] ?? $_SERVER['MAIL_FROM_ADDRESS'] ?? getenv('MAIL_FROM_ADDRESS') ?: 'NOT FOUND') . "\n";

echo '</pre>';
echo '<p style="color:red;font-weight:bold">DELETE THIS FILE AFTER USE!</p>';
