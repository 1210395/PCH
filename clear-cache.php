<?php
/**
 * Cache clearing script — DELETE AFTER USE
 * Visit: https://technopark.ps/PalestineCreativeHub/clear-cache.php
 */

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle($request = Illuminate\Http\Request::capture());

echo "<pre>";

// 1. Clear view cache
$viewPath = storage_path('framework/views');
$count = 0;
if (is_dir($viewPath)) {
    foreach (glob($viewPath . '/*') as $file) {
        if (is_file($file)) {
            unlink($file);
            $count++;
        }
    }
}
echo "Deleted $count compiled view files\n";

// 2. Clear config cache
$configCache = base_path('bootstrap/cache/config.php');
if (file_exists($configCache)) {
    unlink($configCache);
    echo "Deleted config cache\n";
} else {
    echo "No config cache found\n";
}

// 3. Clear route cache
$routeCache = base_path('bootstrap/cache/routes-v7.php');
if (file_exists($routeCache)) {
    unlink($routeCache);
    echo "Deleted route cache\n";
} else {
    echo "No route cache found\n";
}

// 4. Verify the admin layout file content
$adminLayout = resource_path('views/admin/layouts/app.blade.php');
echo "\n=== ADMIN LAYOUT CHECK ===\n";
if (file_exists($adminLayout)) {
    $content = file_get_contents($adminLayout);
    // Show lines 15-21
    $lines = explode("\n", $content);
    for ($i = 14; $i < min(21, count($lines)); $i++) {
        echo "Line " . ($i+1) . ": " . htmlspecialchars($lines[$i]) . "\n";
    }

    if (str_contains($content, '@vite')) {
        echo "\n⚠ WARNING: Layout still uses @vite() — OLD FILE!\n";
    } elseif (str_contains($content, '/PalestineCreativeHub/public/build/')) {
        echo "\n✓ Layout has hardcoded path — NEW FILE\n";
    }
} else {
    echo "Admin layout file NOT found!\n";
}

echo "\nCache cleared. Delete this file now.\n";
echo "</pre>";

$kernel->terminate($request, $response ?? $request);
