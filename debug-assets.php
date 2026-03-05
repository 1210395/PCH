<?php
/**
 * Asset URL Debug Tool
 * Upload to /PalestineCreativeHub/ root and visit:
 * https://technopark.ps/PalestineCreativeHub/debug-assets.php
 *
 * DELETE THIS FILE AFTER DEBUGGING
 */

// Bootstrap Laravel
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle($request = Illuminate\Http\Request::capture());

echo "<pre style='font-family:monospace;font-size:14px;line-height:1.8;'>";
echo "<h2>Asset URL Debug</h2>\n";

// 1. Server variables
echo "<b>=== SERVER VARIABLES ===</b>\n";
echo "SCRIPT_NAME:     " . ($_SERVER['SCRIPT_NAME'] ?? 'NOT SET') . "\n";
echo "SCRIPT_FILENAME: " . ($_SERVER['SCRIPT_FILENAME'] ?? 'NOT SET') . "\n";
echo "REQUEST_URI:     " . ($_SERVER['REQUEST_URI'] ?? 'NOT SET') . "\n";
echo "DOCUMENT_ROOT:   " . ($_SERVER['DOCUMENT_ROOT'] ?? 'NOT SET') . "\n";
echo "PHP_SELF:        " . ($_SERVER['PHP_SELF'] ?? 'NOT SET') . "\n";

// 2. Laravel request info
echo "\n<b>=== LARAVEL REQUEST ===</b>\n";
echo "request->root():        " . $request->root() . "\n";
echo "request->getBaseUrl():  " . $request->getBaseUrl() . "\n";
echo "request->getBasePath(): " . $request->getBasePath() . "\n";
echo "request->getSchemeAndHttpHost(): " . $request->getSchemeAndHttpHost() . "\n";

// 3. Config values
echo "\n<b>=== CONFIG VALUES ===</b>\n";
echo "config('app.url'):       " . config('app.url', 'NOT SET') . "\n";
echo "config('app.asset_url'): " . (config('app.asset_url') ?? 'NULL (not set)') . "\n";
echo "env('APP_URL'):          " . env('APP_URL', 'NOT SET') . "\n";
echo "env('ASSET_URL'):        " . (env('ASSET_URL') ?? 'NULL (not set)') . "\n";

// 4. URL helper outputs
echo "\n<b>=== URL HELPER OUTPUTS ===</b>\n";
echo "asset('build/assets/test.css'):  " . asset('build/assets/test.css') . "\n";
echo "url('build/assets/test.css'):    " . url('build/assets/test.css') . "\n";
echo "public_path():                   " . public_path() . "\n";
echo "public_path('build/manifest.json'): " . public_path('build/manifest.json') . "\n";

// 5. Manifest check
echo "\n<b>=== MANIFEST CHECK ===</b>\n";
$manifestPath = public_path('build/manifest.json');
echo "Manifest exists: " . (file_exists($manifestPath) ? 'YES' : 'NO') . "\n";
if (file_exists($manifestPath)) {
    $manifest = json_decode(file_get_contents($manifestPath), true);
    $adminCss = $manifest['resources/css/admin.css']['file'] ?? 'NOT FOUND';
    echo "Admin CSS file:  " . $adminCss . "\n";

    $cssFullPath = public_path('build/' . $adminCss);
    echo "CSS full path:   " . $cssFullPath . "\n";
    echo "CSS file exists: " . (file_exists($cssFullPath) ? 'YES' : 'NO') . "\n";
} else {
    echo "Manifest NOT found at: " . $manifestPath . "\n";
    // Try alternative paths
    $altPath = __DIR__ . '/public/build/manifest.json';
    echo "Alt path exists: " . (file_exists($altPath) ? 'YES at ' . $altPath : 'NO') . "\n";
}

// 6. What @vite() would generate
echo "\n<b>=== VITE OUTPUT ===</b>\n";
try {
    $vite = app(\Illuminate\Foundation\Vite::class);
    $output = $vite('resources/css/admin.css');
    echo "Vite output: " . htmlspecialchars((string)$output) . "\n";
} catch (\Exception $e) {
    echo "Vite error: " . $e->getMessage() . "\n";
}

// 7. Our manual fix output
echo "\n<b>=== MANUAL FIX OUTPUT ===</b>\n";
if (isset($adminCss) && $adminCss !== 'NOT FOUND') {
    $manualUrl = rtrim(config('app.url'), '/') . '/build/' . $adminCss;
    echo "Manual URL: " . $manualUrl . "\n";
}

// 8. Directory listing
echo "\n<b>=== BUILD DIRECTORY ===</b>\n";
$buildDir = public_path('build/assets');
if (is_dir($buildDir)) {
    echo "Directory exists: YES\n";
    $files = scandir($buildDir);
    foreach ($files as $f) {
        if (str_contains($f, 'admin')) {
            echo "  Found: " . $f . "\n";
        }
    }
} else {
    echo "Directory NOT found: " . $buildDir . "\n";
    // Check alternative
    $altBuild = __DIR__ . '/public/build/assets';
    if (is_dir($altBuild)) {
        echo "Alt directory exists: YES at " . $altBuild . "\n";
        $files = scandir($altBuild);
        foreach ($files as $f) {
            if (str_contains($f, 'admin')) {
                echo "  Found: " . $f . "\n";
            }
        }
    } else {
        echo "Alt directory NOT found: " . $altBuild . "\n";
    }
}

echo "\n</pre>";

$kernel->terminate($request, $response);
