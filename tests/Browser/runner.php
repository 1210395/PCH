<?php
/**
 * Live Test Runner — streams results test by test.
 * Open in browser: http://localhost:8888/runner.php
 *
 * Runs each test method individually so you see progress in real time.
 * Shows pass/fail, duration, screenshot links, and error details.
 */

$projectRoot = realpath(__DIR__ . '/../../');
$screenshotDir = __DIR__ . '/screenshots';

// Get action
$action = $_GET['action'] ?? 'ui';

// ==========================================
// API: List all test methods
// ==========================================
if ($action === 'list') {
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');

    $tests = [];
    foreach (glob(__DIR__ . '/Flow*.php') as $file) {
        $content = file_get_contents($file);
        $class = basename($file, '.php');
        preg_match_all('/public function (test_\w+)/', $content, $matches);
        foreach ($matches[1] as $method) {
            $tests[] = ['class' => $class, 'method' => $method, 'file' => basename($file)];
        }
    }
    echo json_encode($tests);
    exit;
}

// ==========================================
// API: Run a single test
// ==========================================
if ($action === 'run') {
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');

    $filter = $_GET['filter'] ?? '';
    if (!$filter) { echo json_encode(['error' => 'No filter']); exit; }

    $start = microtime(true);
    $cmd = "cd " . escapeshellarg($projectRoot) . " && php artisan dusk --filter=" . escapeshellarg($filter) . " 2>&1";
    $output = shell_exec($cmd);
    $duration = round(microtime(true) - $start, 1);

    // Strip ANSI
    $clean = preg_replace('/\x1b\[[0-9;]*m/', '', $output ?? '');

    // Parse result
    $passed = preg_match('/\d+ passed/', $clean);
    $failed = preg_match('/\d+ failed/', $clean);
    $status = $failed ? 'fail' : ($passed ? 'pass' : 'error');

    // Extract error message
    $error = '';
    if (preg_match('/FAILED.*?\n(.*?)(?=\n\n|\n  Tests:)/s', $clean, $m)) {
        $error = trim($m[1]);
    }

    echo json_encode([
        'status' => $status,
        'duration' => $duration . 's',
        'output' => substr($clean, 0, 3000),
        'error' => $error,
    ]);
    exit;
}

// ==========================================
// API: Get screenshot
// ==========================================
if ($action === 'screenshot') {
    $name = basename($_GET['name'] ?? '');
    // Search in subdirectories
    $found = null;
    foreach (glob($screenshotDir . '/**/*.png') + glob($screenshotDir . '/*.png') as $f) {
        if (basename($f, '.png') === $name || str_replace('/', '-', str_replace($screenshotDir . '/', '', $f)) === $name . '.png') {
            $found = $f;
            break;
        }
    }
    // Also search by path pattern
    if (!$found) {
        $pattern = str_replace('-', '/', $name);
        $try = $screenshotDir . '/' . $pattern . '.png';
        if (file_exists($try)) $found = $try;
    }

    if ($found && file_exists($found)) {
        header('Content-Type: image/png');
        readfile($found);
    } else {
        header('HTTP/1.1 404 Not Found');
        echo 'Not found: ' . $name;
    }
    exit;
}

// ==========================================
// API: List screenshots
// ==========================================
if ($action === 'screenshots') {
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');

    $files = [];
    foreach (glob($screenshotDir . '/{,*/,*/*/}*.png', GLOB_BRACE) as $f) {
        $rel = str_replace($screenshotDir . '/', '', $f);
        $files[] = $rel;
    }
    echo json_encode($files);
    exit;
}

// ==========================================
// API: Stop tests
// ==========================================
if ($action === 'stop') {
    header('Content-Type: application/json');
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        exec('taskkill /F /IM chromedriver-win.exe 2>NUL');
        exec('taskkill /F /IM chromedriver.exe 2>NUL');
    } else {
        exec('pkill -f chromedriver 2>/dev/null');
    }
    echo json_encode(['stopped' => true]);
    exit;
}

// ==========================================
// UI: Dashboard
// ==========================================
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Test Runner</title>
<script src="https://cdn.tailwindcss.com"></script>
<style>
body{font-family:'Segoe UI',system-ui,sans-serif}
.pass{color:#16a34a}.fail{color:#dc2626}.running{color:#2563eb}
.test-row{transition:all .2s}
.test-row:hover{background:#f8fafc}
.new{animation:slideIn .3s ease-out}
@keyframes slideIn{from{opacity:0;transform:translateY(-10px)}to{opacity:1;transform:none}}
.pulse{animation:pulse 1s infinite}
@keyframes pulse{0%,100%{opacity:1}50%{opacity:.4}}
pre{white-space:pre-wrap;word-break:break-all;font-size:11px;max-height:300px;overflow:auto}
</style>
</head>
<body class="bg-gray-50 min-h-screen">

<header class="bg-white border-b shadow-sm sticky top-0 z-40">
<div class="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between flex-wrap gap-2">
    <div>
        <h1 class="text-lg font-bold text-gray-800">Test Runner</h1>
        <p id="status" class="text-sm text-gray-500">Ready</p>
    </div>
    <div class="flex gap-2">
        <button onclick="runAll()" id="btn-run" class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700">Run All</button>
        <button onclick="stopAll()" class="px-3 py-2 bg-red-100 text-red-700 rounded-lg text-sm hover:bg-red-200">Stop</button>
        <button onclick="clearResults()" class="px-3 py-2 bg-gray-200 text-gray-700 rounded-lg text-sm hover:bg-gray-300">Clear</button>
    </div>
</div>
</header>

<!-- Stats -->
<div class="max-w-6xl mx-auto px-4 py-3 grid grid-cols-5 gap-3">
    <div class="bg-white rounded-lg shadow-sm p-3 text-center"><div id="s-total" class="text-2xl font-bold">0</div><div class="text-[10px] text-gray-500">Total</div></div>
    <div class="bg-white rounded-lg shadow-sm p-3 text-center"><div id="s-pass" class="text-2xl font-bold text-green-600">0</div><div class="text-[10px] text-gray-500">Passed</div></div>
    <div class="bg-white rounded-lg shadow-sm p-3 text-center"><div id="s-fail" class="text-2xl font-bold text-red-600">0</div><div class="text-[10px] text-gray-500">Failed</div></div>
    <div class="bg-white rounded-lg shadow-sm p-3 text-center"><div id="s-rate" class="text-2xl font-bold text-blue-600">-</div><div class="text-[10px] text-gray-500">Rate</div></div>
    <div class="bg-white rounded-lg shadow-sm p-3 text-center"><div id="s-time" class="text-2xl font-bold text-purple-600">0:00</div><div class="text-[10px] text-gray-500">Elapsed</div></div>
</div>

<!-- Progress -->
<div class="max-w-6xl mx-auto px-4 pb-3">
    <div class="bg-gray-200 rounded-full h-2.5 overflow-hidden flex">
        <div id="bar-pass" class="h-full bg-green-500 transition-all" style="width:0"></div>
        <div id="bar-fail" class="h-full bg-red-500 transition-all" style="width:0"></div>
    </div>
    <div id="current-test" class="text-xs text-gray-500 mt-1 hidden"><span class="pulse text-blue-600">Running:</span> <span id="ct-name" class="font-mono"></span></div>
</div>

<!-- Test Results -->
<div class="max-w-6xl mx-auto px-4 pb-8">
<div class="bg-white rounded-lg shadow-sm overflow-hidden">
    <div class="px-4 py-2 border-b bg-gray-50 flex justify-between items-center">
        <span class="font-semibold text-sm text-gray-700">Tests</span>
        <div class="flex gap-2">
            <button onclick="filter='all';render()" class="text-xs px-2 py-1 rounded bg-gray-100 hover:bg-gray-200">All</button>
            <button onclick="filter='fail';render()" class="text-xs px-2 py-1 rounded bg-red-100 text-red-700 hover:bg-red-200">Failed</button>
        </div>
    </div>
    <div id="results" class="divide-y text-sm"></div>
    <div id="empty" class="p-6 text-center text-gray-400 text-sm">Click <b>Run All</b> to start</div>
</div>
</div>

<!-- Screenshot Modal -->
<div id="ss-modal" class="fixed inset-0 z-50 bg-black/80 hidden items-center justify-center" onclick="this.classList.add('hidden');this.classList.remove('flex')">
    <img id="ss-img" class="max-w-[95vw] max-h-[90vh] object-contain rounded-lg shadow-2xl">
</div>

<!-- Error Detail Modal -->
<div id="err-modal" class="fixed inset-0 z-50 bg-black/50 hidden items-center justify-center" onclick="if(event.target===this){this.classList.add('hidden');this.classList.remove('flex')}">
    <div class="bg-white rounded-lg max-w-3xl w-full mx-4 max-h-[80vh] overflow-auto p-5">
        <h3 id="err-title" class="font-bold text-red-600 text-sm mb-3"></h3>
        <pre id="err-body" class="bg-red-50 border border-red-200 rounded p-3 text-red-800"></pre>
    </div>
</div>

<script>
let tests = [];
let results = {};
let running = false;
let aborted = false;
let startTime = 0;
let timer = null;
let total = 0, passed = 0, failed = 0;
let filter = 'all';

async function loadTests() {
    const res = await fetch('runner.php?action=list');
    tests = await res.json();
    total = tests.length;
    document.getElementById('s-total').textContent = total;
    document.getElementById('status').textContent = total + ' tests found';
}

function updateStats() {
    document.getElementById('s-total').textContent = total;
    document.getElementById('s-pass').textContent = passed;
    document.getElementById('s-fail').textContent = failed;
    const done = passed + failed;
    document.getElementById('s-rate').textContent = done > 0 ? Math.round(passed/done*100)+'%' : '-';

    if (total > 0) {
        document.getElementById('bar-pass').style.width = (passed/total*100)+'%';
        document.getElementById('bar-fail').style.width = (failed/total*100)+'%';
    }
}

function updateTimer() {
    const elapsed = Math.floor((Date.now() - startTime) / 1000);
    const m = Math.floor(elapsed / 60);
    const s = elapsed % 60;
    document.getElementById('s-time').textContent = m + ':' + String(s).padStart(2,'0');
}

function render() {
    const el = document.getElementById('results');
    const entries = Object.entries(results);

    if (entries.length === 0) {
        document.getElementById('empty').classList.remove('hidden');
        el.innerHTML = '';
        return;
    }

    document.getElementById('empty').classList.add('hidden');
    let html = '';
    let lastFile = '';

    for (const [key, r] of entries) {
        if (filter === 'fail' && r.status !== 'fail') continue;

        const t = tests.find(t => t.method === key);
        const file = t ? t.file : '';

        // File header
        if (file !== lastFile) {
            const fileResults = entries.filter(([k,v]) => { const tt = tests.find(x=>x.method===k); return tt && tt.file === file; });
            const fp = fileResults.filter(([k,v]) => v.status === 'pass').length;
            const ff = fileResults.filter(([k,v]) => v.status === 'fail').length;
            const ft = fileResults.length;
            const color = ff > 0 ? 'text-red-600' : (fp === ft ? 'text-green-600' : 'text-gray-600');
            html += '<div class="px-4 py-1.5 bg-gray-50 border-b flex justify-between"><span class="font-mono text-xs text-gray-500">' + file + '</span><span class="text-xs font-medium '+color+'">' + fp + '/' + ft + '</span></div>';
            lastFile = file;
        }

        const icon = r.status === 'pass' ? '<span class="text-green-500 text-base">&#10003;</span>' :
                     r.status === 'fail' ? '<span class="text-red-500 text-base">&#10007;</span>' :
                     '<span class="text-blue-500 pulse text-base">&#9679;</span>';

        const name = key.replace('test_', '').replace(/_/g, ' ');
        const dur = r.duration || '';
        const ssName = name.replace(/\s+/g, '-').toLowerCase();

        html += '<div class="test-row px-4 py-2 flex items-center gap-3 ' + (r.status === 'fail' ? 'bg-red-50' : '') + ' new">';
        html += icon;
        html += '<span class="flex-1 text-gray-700">' + name + '</span>';
        html += '<span class="text-xs text-gray-400 w-12 text-right">' + dur + '</span>';
        if (r.status === 'fail' && r.error) {
            html += '<button class="text-xs px-2 py-0.5 bg-red-100 text-red-700 rounded hover:bg-red-200" onclick="showError(\'' + key.replace(/'/g,"\\'") + '\')">Error</button>';
        }
        html += '<button class="text-xs px-2 py-0.5 bg-gray-100 text-gray-600 rounded hover:bg-gray-200" onclick="showScreenshot(\'' + ssName + '\')">Shot</button>';
        html += '</div>';
    }

    el.innerHTML = html;
}

async function runAll() {
    if (running) return;
    running = true;
    aborted = false;
    passed = 0;
    failed = 0;
    results = {};
    startTime = Date.now();
    timer = setInterval(updateTimer, 1000);

    document.getElementById('btn-run').disabled = true;
    document.getElementById('btn-run').textContent = 'Running...';
    document.getElementById('btn-run').classList.replace('bg-green-600', 'bg-gray-400');
    document.getElementById('current-test').classList.remove('hidden');

    render();
    updateStats();

    for (const test of tests) {
        if (aborted) break;

        const key = test.method;
        const name = key.replace('test_', '').replace(/_/g, ' ');

        document.getElementById('ct-name').textContent = test.class + ' > ' + name;
        document.getElementById('status').textContent = 'Running ' + (passed+failed+1) + '/' + total + '...';

        results[key] = { status: 'running', duration: '', error: '' };
        render();

        try {
            const res = await fetch('runner.php?action=run&filter=' + encodeURIComponent(key));
            const data = await res.json();
            results[key] = data;
            if (data.status === 'pass') passed++;
            else failed++;
        } catch (e) {
            results[key] = { status: 'fail', duration: '-', error: e.message };
            failed++;
        }

        updateStats();
        render();

        // Scroll to bottom
        const container = document.getElementById('results');
        container.scrollTop = container.scrollHeight;
    }

    running = false;
    clearInterval(timer);
    document.getElementById('btn-run').disabled = false;
    document.getElementById('btn-run').textContent = 'Run All';
    document.getElementById('btn-run').classList.replace('bg-gray-400', 'bg-green-600');
    document.getElementById('current-test').classList.add('hidden');
    document.getElementById('status').textContent = aborted ? 'Stopped' : 'Done: ' + passed + ' passed, ' + failed + ' failed';
}

function stopAll() {
    aborted = true;
    fetch('runner.php?action=stop');
    document.getElementById('status').textContent = 'Stopping...';
}

function clearResults() {
    results = {};
    passed = 0;
    failed = 0;
    render();
    updateStats();
    document.getElementById('status').textContent = total + ' tests ready';
    document.getElementById('s-time').textContent = '0:00';
}

function showScreenshot(name) {
    const img = document.getElementById('ss-img');
    // Try multiple path formats
    const paths = [name, name.replace(/-/g, '/')];
    let idx = 0;

    function tryNext() {
        if (idx >= paths.length) {
            alert('Screenshot not found: ' + name);
            return;
        }
        img.src = 'runner.php?action=screenshot&name=' + encodeURIComponent(paths[idx]);
        idx++;
    }

    img.onerror = tryNext;
    img.onload = function() {
        document.getElementById('ss-modal').classList.remove('hidden');
        document.getElementById('ss-modal').classList.add('flex');
    };
    tryNext();
}

function showError(key) {
    const r = results[key];
    if (!r) return;
    document.getElementById('err-title').textContent = 'FAILED: ' + key.replace('test_','').replace(/_/g,' ');
    document.getElementById('err-body').textContent = r.error || r.output || 'No details';
    document.getElementById('err-modal').classList.remove('hidden');
    document.getElementById('err-modal').classList.add('flex');
}

// Init
loadTests();
</script>
</body>
</html>
