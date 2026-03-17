<?php
/**
 * Image Debug & Cache Clear Tool
 * Upload to: public/test-images.php
 * Access via: https://technopark.ps/PalestineCreativeHub/test-images.php
 * DELETE THIS FILE AFTER USE
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$action = $_GET['action'] ?? 'diagnose';

echo '<html><head><title>Image Debug Tool</title>';
echo '<style>body{font-family:monospace;padding:20px;max-width:1200px;margin:0 auto}
.ok{color:green;font-weight:bold}.err{color:red;font-weight:bold}.warn{color:orange;font-weight:bold}
pre{background:#f5f5f5;padding:15px;border-radius:5px;overflow-x:auto}
img{max-width:200px;max-height:120px;border:2px solid #ccc;margin:5px}
.img-fail{border-color:red}
h2{border-bottom:2px solid #333;padding-bottom:5px}
a.btn{display:inline-block;padding:8px 16px;background:#2563eb;color:white;text-decoration:none;border-radius:5px;margin:5px}
a.btn:hover{background:#1d4ed8}
a.btn.red{background:#dc2626}a.btn.red:hover{background:#b91c1c}
</style></head><body>';

// ============================================================
// ACTION: Clear all caches
// ============================================================
if ($action === 'clear') {
    echo '<h1>Clearing Caches...</h1>';

    $commands = [
        'config:clear' => 'Config cache',
        'view:clear' => 'View cache',
        'cache:clear' => 'Application cache',
        'route:clear' => 'Route cache',
    ];

    foreach ($commands as $cmd => $label) {
        try {
            \Illuminate\Support\Facades\Artisan::call($cmd);
            $output = trim(\Illuminate\Support\Facades\Artisan::output());
            echo "<p><span class='ok'>OK</span> - $label cleared" . ($output ? ": $output" : "") . "</p>";
        } catch (\Exception $e) {
            echo "<p><span class='err'>FAIL</span> - $label: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    }

    // Now rebuild config cache
    try {
        \Illuminate\Support\Facades\Artisan::call('config:cache');
        echo "<p><span class='ok'>OK</span> - Config re-cached</p>";
    } catch (\Exception $e) {
        echo "<p><span class='warn'>WARN</span> - Could not re-cache config: " . htmlspecialchars($e->getMessage()) . "</p>";
    }

    echo '<br><a class="btn" href="?action=diagnose">Run Diagnostics</a>';
    echo '<br><br><p><b>Now refresh your site pages to see if images load.</b></p>';
}

// ============================================================
// ACTION: Diagnose
// ============================================================
elseif ($action === 'diagnose') {
    echo '<h1>Image Diagnostics</h1>';
    echo '<a class="btn red" href="?action=clear">Clear All Caches</a> ';
    echo '<a class="btn" href="?action=diagnose">Refresh Diagnostics</a>';

    // --- Config Values ---
    echo '<h2>1. Config Values</h2>';
    $appUrl = config('app.url');
    $assetUrl = config('app.asset_url');
    $envAppUrl = env('APP_URL');
    $envAssetUrl = env('ASSET_URL');

    echo "<pre>";
    echo "config('app.url')       = " . var_export($appUrl, true) . "\n";
    echo "config('app.asset_url') = " . var_export($assetUrl, true) . "\n";
    echo "env('APP_URL')          = " . var_export($envAppUrl, true) . "\n";
    echo "env('ASSET_URL')        = " . var_export($envAssetUrl, true) . "\n";
    echo "</pre>";

    if (empty($assetUrl)) {
        echo "<p class='err'>ASSET_URL is empty! asset() will generate relative URLs. This is likely the cause of broken images.</p>";
    } elseif ($assetUrl !== $appUrl) {
        echo "<p class='warn'>ASSET_URL differs from APP_URL. Make sure ASSET_URL is correct.</p>";
    } else {
        echo "<p class='ok'>ASSET_URL is set correctly.</p>";
    }

    $configCached = file_exists(base_path('bootstrap/cache/config.php'));
    echo "<p>Config cached: " . ($configCached ? "<span class='warn'>YES</span> (cached values override .env)" : "<span class='ok'>NO</span> (reading from .env directly)") . "</p>";

    if ($configCached) {
        $cachedConfig = require base_path('bootstrap/cache/config.php');
        $cachedAssetUrl = $cachedConfig['app']['asset_url'] ?? null;
        $cachedAppUrl = $cachedConfig['app']['url'] ?? null;
        echo "<pre>Cached app.url       = " . var_export($cachedAppUrl, true) . "\n";
        echo "Cached app.asset_url = " . var_export($cachedAssetUrl, true) . "</pre>";

        if (empty($cachedAssetUrl)) {
            echo "<p class='err'>CACHED ASSET_URL is empty! This is the problem. Click 'Clear All Caches' above.</p>";
        }
    }

    // --- URL Generation Test ---
    echo '<h2>2. URL Generation Test</h2>';
    $testPath = 'profiles/profile_1.jpg';
    echo "<pre>";
    echo "asset('storage/$testPath')  = " . asset('storage/' . $testPath) . "\n";
    echo "url('storage/$testPath')    = " . url('storage/' . $testPath) . "\n";
    echo "</pre>";

    $assetResult = asset('storage/' . $testPath);
    $urlResult = url('storage/' . $testPath);

    if (parse_url($assetResult, PHP_URL_HOST)) {
        echo "<p class='ok'>asset() generates absolute URL with host.</p>";
    } else {
        echo "<p class='err'>asset() generates RELATIVE URL (no host). This causes the /en/ prefix issue!</p>";
    }

    if ($assetResult === $urlResult) {
        echo "<p class='ok'>asset() and url() produce the same result.</p>";
    } else {
        echo "<p class='warn'>asset() and url() produce DIFFERENT results!</p>";
    }

    // --- Storage Path Test ---
    echo '<h2>3. Storage Paths</h2>';
    $storagePath = storage_path('app/public');
    echo "<pre>storage_path('app/public') = $storagePath</pre>";
    echo "<p>Directory exists: " . (is_dir($storagePath) ? "<span class='ok'>YES</span>" : "<span class='err'>NO</span>") . "</p>";
    echo "<p>Readable: " . (is_readable($storagePath) ? "<span class='ok'>YES</span>" : "<span class='err'>NO</span>") . "</p>";

    // List subdirectories
    if (is_dir($storagePath)) {
        $dirs = array_filter(glob($storagePath . '/*'), 'is_dir');
        echo "<p>Subdirectories:</p><ul>";
        foreach ($dirs as $dir) {
            $name = basename($dir);
            $count = count(glob($dir . '/*'));
            $perms = substr(sprintf('%o', fileperms($dir)), -4);
            echo "<li><b>$name/</b> ($count files, perms: $perms)</li>";
        }
        echo "</ul>";
    }

    // --- Symlink Check ---
    echo '<h2>4. Public Storage Symlink</h2>';
    $symlinkPath = public_path('storage');
    if (is_link($symlinkPath)) {
        $target = readlink($symlinkPath);
        echo "<p class='warn'>Symlink EXISTS: public/storage -> $target</p>";
        echo "<p>This means Apache may serve files directly (bypassing Laravel route). Check file permissions.</p>";
    } elseif (is_dir($symlinkPath)) {
        echo "<p class='warn'>public/storage is a real directory (not a symlink).</p>";
    } else {
        echo "<p class='ok'>No symlink at public/storage. Images served through Laravel route.</p>";
    }

    // --- Real Image Test ---
    echo '<h2>5. Image Loading Test</h2>';

    // Find some real images
    $testImages = [];
    $folders = ['profiles', 'products', 'projects', 'covers', 'fablabs', 'hero_images'];
    foreach ($folders as $folder) {
        $folderPath = $storagePath . '/' . $folder;
        if (is_dir($folderPath)) {
            $files = glob($folderPath . '/*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE);
            if (!empty($files)) {
                $file = basename($files[0]);
                $relativePath = $folder . '/' . $file;
                $testImages[$folder] = $relativePath;
            }
        }
    }

    if (empty($testImages)) {
        echo "<p class='err'>No images found in storage/app/public/ subdirectories!</p>";
    } else {
        echo '<table border="1" cellpadding="8" cellspacing="0">';
        echo '<tr><th>Folder</th><th>asset() URL</th><th>url() URL</th><th>asset() Image</th><th>url() Image</th></tr>';
        foreach ($testImages as $folder => $path) {
            $assetSrc = asset('storage/' . $path);
            $urlSrc = url('storage/' . $path);
            echo "<tr>";
            echo "<td><b>$folder</b></td>";
            echo "<td style='font-size:11px;max-width:300px;word-break:break-all'>$assetSrc</td>";
            echo "<td style='font-size:11px;max-width:300px;word-break:break-all'>$urlSrc</td>";
            echo "<td><img src='$assetSrc' onerror=\"this.className='img-fail';this.alt='FAILED'\"></td>";
            echo "<td><img src='$urlSrc' onerror=\"this.className='img-fail';this.alt='FAILED'\"></td>";
            echo "</tr>";
        }
        echo '</table>';
    }

    // --- .htaccess Check ---
    echo '<h2>6. Rewrite Rules</h2>';
    $htaccess = public_path('.htaccess');
    if (file_exists($htaccess)) {
        $content = file_get_contents($htaccess);
        if (preg_match('/RewriteBase\s+(.+)/', $content, $m)) {
            echo "<p>RewriteBase: <b>" . trim($m[1]) . "</b></p>";
        } else {
            echo "<p class='warn'>No RewriteBase found in .htaccess</p>";
        }
    }
}

echo '<hr><p style="color:red"><b>DELETE this file after debugging!</b></p>';
echo '</body></html>';
