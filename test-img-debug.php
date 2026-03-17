<?php
/**
 * Image Debug Tool v2
 * DELETE THIS FILE AFTER USE
 */
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo '<html><head><title>Image Debug v2</title>';
echo '<style>body{font-family:monospace;padding:20px;max-width:1200px;margin:0 auto}
.ok{color:green;font-weight:bold}.err{color:red;font-weight:bold}.warn{color:orange;font-weight:bold}
pre{background:#f5f5f5;padding:10px;border-radius:5px;overflow-x:auto;font-size:12px}
img{max-width:150px;max-height:100px;border:2px solid #ccc;margin:3px}
h2{border-bottom:2px solid #333;padding-bottom:5px;margin-top:30px}
a.btn{display:inline-block;padding:8px 16px;background:#2563eb;color:white;text-decoration:none;border-radius:5px;margin:5px}
a.btn.red{background:#dc2626}
table{border-collapse:collapse}td,th{border:1px solid #ccc;padding:6px;font-size:12px}
</style></head><body>';
echo '<h1>Image Debug v2</h1>';

// ============================================================
// 1. The known working URL
// ============================================================
echo '<h2>1. Known Working vs Failing</h2>';
$workingFile = 'products/product_473_1.jpg';
$storagePath = storage_path('app/public');

echo '<pre>';
echo "Working file: $workingFile\n";
echo "Full path: $storagePath/$workingFile\n";
echo "Exists: " . (file_exists("$storagePath/$workingFile") ? 'YES' : 'NO') . "\n";
if (file_exists("$storagePath/$workingFile")) {
    echo "Perms: " . substr(sprintf('%o', fileperms("$storagePath/$workingFile")), -4) . "\n";
    echo "Owner: " . fileowner("$storagePath/$workingFile") . "\n";
    echo "Size: " . filesize("$storagePath/$workingFile") . " bytes\n";
    echo "Readable: " . (is_readable("$storagePath/$workingFile") ? 'YES' : 'NO') . "\n";
    echo "realpath: " . var_export(realpath("$storagePath/$workingFile"), true) . "\n";
}
echo '</pre>';

echo '<p>Direct URL test: <img src="' . url('storage/' . $workingFile) . '" onerror="this.alt=\'FAILED\'" alt="loading..."></p>';

// ============================================================
// 2. First file from each folder - compare permissions
// ============================================================
echo '<h2>2. File Permission Comparison</h2>';
echo '<table><tr><th>Folder</th><th>File</th><th>Perms</th><th>Owner</th><th>Group</th><th>Size</th><th>Readable</th><th>realpath</th><th>Image</th></tr>';

$folders = ['profiles', 'products', 'projects', 'covers', 'fablabs', 'hero_images'];
foreach ($folders as $folder) {
    $folderPath = "$storagePath/$folder";
    if (!is_dir($folderPath)) { echo "<tr><td>$folder</td><td colspan='8' class='err'>Directory not found</td></tr>"; continue; }

    $files = glob("$folderPath/*.{jpg,jpeg,png,gif,webp}", GLOB_BRACE);
    if (empty($files)) { echo "<tr><td>$folder</td><td colspan='8' class='warn'>No images</td></tr>"; continue; }

    // Show first file AND a structured-name file if available
    $testFiles = [basename($files[0])];
    foreach ($files as $f) {
        $bn = basename($f);
        if (preg_match('/^(product|project|profile|hero)_\d+/', $bn)) {
            $testFiles[] = $bn;
            break;
        }
    }
    $testFiles = array_unique($testFiles);

    foreach ($testFiles as $filename) {
        $fullPath = "$folderPath/$filename";
        $relativePath = "$folder/$filename";
        $perms = substr(sprintf('%o', fileperms($fullPath)), -4);
        $owner = fileowner($fullPath);
        $group = filegroup($fullPath);
        $size = filesize($fullPath);
        $readable = is_readable($fullPath) ? 'YES' : 'NO';
        $rp = realpath($fullPath) ? 'OK' : 'FAIL';
        $imgUrl = url("storage/$relativePath");

        echo "<tr>";
        echo "<td><b>$folder</b></td>";
        echo "<td>$filename</td>";
        echo "<td>$perms</td>";
        echo "<td>$owner</td>";
        echo "<td>$group</td>";
        echo "<td>$size</td>";
        echo "<td>" . ($readable === 'YES' ? "<span class='ok'>YES</span>" : "<span class='err'>NO</span>") . "</td>";
        echo "<td>" . ($rp === 'OK' ? "<span class='ok'>OK</span>" : "<span class='err'>FAIL</span>") . "</td>";
        echo "<td><img src='$imgUrl' onerror=\"this.className='err';this.alt='FAIL'\" alt='loading'></td>";
        echo "</tr>";
    }
}
echo '</table>';

// ============================================================
// 3. public/storage directory investigation
// ============================================================
echo '<h2>3. public/storage Investigation</h2>';
$publicStorage = __DIR__ . '/public/storage';
echo '<pre>';
echo "Path: $publicStorage\n";
echo "is_link: " . var_export(is_link($publicStorage), true) . "\n";
echo "is_dir: " . var_export(is_dir($publicStorage), true) . "\n";
echo "is_file: " . var_export(is_file($publicStorage), true) . "\n";
echo "file_exists: " . var_export(file_exists($publicStorage), true) . "\n";

if (is_dir($publicStorage)) {
    $contents = @scandir($publicStorage);
    echo "Contents: " . var_export($contents, true) . "\n";

    // Check if profiles exists inside
    $subdir = "$publicStorage/profiles";
    echo "\npublic/storage/profiles exists: " . var_export(is_dir($subdir), true) . "\n";
    if (is_dir($subdir)) {
        $subfiles = array_slice(array_diff(scandir($subdir), ['.', '..']), 0, 5);
        echo "First 5 files: " . implode(', ', $subfiles) . "\n";
    }
}
echo '</pre>';

// ============================================================
// 4. Root .htaccess rewrite trace
// ============================================================
echo '<h2>4. Apache Rewrite Analysis</h2>';
echo '<pre>';
$rootHtaccess = __DIR__ . '/.htaccess';
echo "Root .htaccess exists: " . var_export(file_exists($rootHtaccess), true) . "\n\n";
if (file_exists($rootHtaccess)) {
    echo htmlspecialchars(file_get_contents($rootHtaccess));
}
echo '</pre>';

// ============================================================
// 5. Direct file serve test (bypass route)
// ============================================================
echo '<h2>5. Direct PHP File Read Test</h2>';
$testFile = null;
foreach (['products/product_473_1.jpg', 'profiles/profile_1.jpg'] as $tf) {
    if (file_exists("$storagePath/$tf")) { $testFile = $tf; break; }
}
if (!$testFile) {
    $files = glob("$storagePath/products/*.jpg");
    if (!empty($files)) $testFile = 'products/' . basename($files[0]);
}

if ($testFile) {
    $fullTestPath = "$storagePath/$testFile";
    echo "<pre>Testing: $testFile\n";
    echo "Full path: $fullTestPath\n";
    echo "file_exists: " . var_export(file_exists($fullTestPath), true) . "\n";
    echo "is_readable: " . var_export(is_readable($fullTestPath), true) . "\n";
    $rp = realpath($fullTestPath);
    echo "realpath: " . var_export($rp, true) . "\n";
    $basePath = realpath($storagePath);
    echo "basePath: " . var_export($basePath, true) . "\n";
    if ($rp && $basePath) {
        echo "starts_with check: str_starts_with('$rp', '$basePath/')\n";
        echo "Result: " . var_export(str_starts_with($rp, $basePath . DIRECTORY_SEPARATOR), true) . "\n";
    }
    echo "mime_type: " . var_export(@mime_content_type($fullTestPath), true) . "\n";
    echo '</pre>';

    // Serve test image directly from PHP
    echo '<p>Direct PHP serve (no route): ';
    echo '<img src="?action=serve-test&file=' . urlencode($testFile) . '" onerror="this.alt=\'FAILED\'" alt="loading...">';
    echo '</p>';
}

// ============================================================
// 6. HTTP response test
// ============================================================
echo '<h2>6. HTTP Response Check</h2>';
$testUrls = [
    'storage/products/product_473_1.jpg',
];
// Find a real file
$realFiles = glob("$storagePath/profiles/profile_*.jpg");
if (!empty($realFiles)) {
    $testUrls[] = 'storage/profiles/' . basename($realFiles[0]);
}

foreach ($testUrls as $path) {
    $fullUrl = url($path);
    echo "<pre>URL: $fullUrl\n";
    $ch = curl_init($fullUrl);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $finalUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    curl_close($ch);

    $class = $httpCode === 200 ? 'ok' : 'err';
    echo "HTTP Status: <span class='$class'>$httpCode</span>\n";
    echo "Final URL: $finalUrl\n";
    echo "Content-Type: $contentType\n";
    echo '</pre>';
}

// ============================================================
// Serve test image action
// ============================================================
if (($_GET['action'] ?? '') === 'serve-test' && isset($_GET['file'])) {
    $file = $_GET['file'];
    $path = storage_path('app/public/' . $file);
    if (file_exists($path) && is_readable($path)) {
        header('Content-Type: ' . mime_content_type($path));
        readfile($path);
    } else {
        header('HTTP/1.1 404 Not Found');
        echo '404';
    }
    exit;
}

// ============================================================
// Fix symlink action
// ============================================================
if (($_GET['action'] ?? '') === 'fix-symlink') {
    $publicStorage = __DIR__ . '/public/storage';
    $target = __DIR__ . '/storage/app/public';

    echo '<h2>Fix Attempt</h2><pre>';

    if (is_dir($publicStorage) && !is_link($publicStorage)) {
        echo "Removing public/storage directory...\n";
        $it = new RecursiveDirectoryIterator($publicStorage, RecursiveDirectoryIterator::SKIP_DOTS);
        $ri = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($ri as $file) {
            echo "  Removing: " . $file->getRealPath() . "\n";
            $file->isDir() ? @rmdir($file->getRealPath()) : @unlink($file->getRealPath());
        }
        $result = @rmdir($publicStorage);
        echo "rmdir result: " . var_export($result, true) . "\n";
        if (!$result) {
            echo "ERROR: " . error_get_last()['message'] . "\n";
        }
    }

    if (!file_exists($publicStorage)) {
        echo "Creating symlink...\n";
        echo "Target: $target\n";
        echo "Link: $publicStorage\n";
        $result = @symlink($target, $publicStorage);
        echo "symlink result: " . var_export($result, true) . "\n";
        if (!$result) {
            echo "ERROR: " . error_get_last()['message'] . "\n";
        }
    } else {
        echo "public/storage still exists after removal attempt.\n";
        echo "is_dir: " . var_export(is_dir($publicStorage), true) . "\n";
        echo "is_link: " . var_export(is_link($publicStorage), true) . "\n";
    }
    echo '</pre>';
}

echo '<hr>';
echo '<a class="btn red" href="?action=fix-symlink">Try Fix Symlink</a> ';
echo '<a class="btn" href="?">Refresh Diagnostics</a>';
echo '<p style="color:red"><b>DELETE this file after debugging!</b></p>';
echo '</body></html>';
