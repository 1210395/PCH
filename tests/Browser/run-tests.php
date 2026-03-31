<?php
/**
 * Dusk Test Runner Backend.
 * Uses Server-Sent Events (SSE) to stream test results in real-time.
 * Each test result is sent to the browser the moment it completes.
 */

$action = $_GET['action'] ?? 'status';
$isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
$dir = __DIR__;
$projectRoot = realpath($dir . '/../../');
$currentResult = $dir . '/test-results-latest.txt';
$pidFile = $dir . '/test-run.pid';
$historyDir = $dir . '/results-history';
if (!is_dir($historyDir)) @mkdir($historyDir, 0755, true);

switch ($action) {

    // ==========================================
    // STREAM - SSE endpoint that runs tests and streams results live
    // ==========================================
    case 'stream':
        // Save old results
        if (file_exists($currentResult) && filesize($currentResult) > 100) {
            copy($currentResult, $historyDir . '/results_' . date('Y-m-d_H-i-s') . '.txt');
        }

        // Kill old runs
        killTests($isWindows);

        header('Access-Control-Allow-Origin: *');
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
        header('X-Accel-Buffering: no');

        // Disable all buffering
        @ini_set('output_buffering', 'off');
        @ini_set('zlib.output_compression', false);
        if (function_exists('apache_setenv')) @apache_setenv('no-gzip', 1);
        while (ob_get_level()) ob_end_flush();

        set_time_limit(0);
        ignore_user_abort(false);

        sendSSE('status', json_encode(['msg' => 'Starting tests...', 'phase' => 'starting']));

        $descriptors = [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
        $proc = proc_open('php artisan dusk', $descriptors, $pipes, $projectRoot);

        if (!is_resource($proc)) {
            sendSSE('error', json_encode(['msg' => 'Failed to start php artisan dusk']));
            break;
        }

        fclose($pipes[0]);
        stream_set_blocking($pipes[1], false);
        stream_set_blocking($pipes[2], false);

        file_put_contents($pidFile, json_encode(['started' => time(), 'status' => 'running']));
        file_put_contents($currentResult, '');

        $buffer = '';
        $testCount = 0;
        $passCount = 0;
        $failCount = 0;

        sendSSE('status', json_encode(['msg' => 'Tests running...', 'phase' => 'running']));

        while (true) {
            $status = proc_get_status($proc);
            $out = stream_get_contents($pipes[1]);
            $err = stream_get_contents($pipes[2]);

            $chunk = $out . $err;
            if ($chunk !== '' && $chunk !== false) {
                file_put_contents($currentResult, $chunk, FILE_APPEND);
                $buffer .= $chunk;

                // Parse complete lines from buffer
                while (($pos = strpos($buffer, "\n")) !== false) {
                    $line = substr($buffer, 0, $pos);
                    $buffer = substr($buffer, $pos + 1);

                    $clean = preg_replace('/\x1b\[[0-9;]*m/', '', $line);
                    $clean = trim($clean);
                    if (!$clean) continue;

                    // File header
                    if (preg_match('/(?:PASS|FAIL)\s+Tests\\\\Browser\\\\(\w+)/', $clean, $m)) {
                        sendSSE('file', json_encode(['file' => $m[1], 'status' => strpos($clean, 'FAIL') !== false ? 'fail' : 'pass']));
                        continue;
                    }

                    // Passing test
                    if (preg_match('/^[\x{2713}\x{2714}\x{2705}]\s+(.+?)\s+([\d.]+s)\s*$/u', $clean, $m)) {
                        $testCount++; $passCount++;
                        sendSSE('test', json_encode([
                            'name' => trim($m[1]), 'status' => 'pass', 'duration' => $m[2],
                            'total' => $testCount, 'passed' => $passCount, 'failed' => $failCount
                        ]));
                        continue;
                    }

                    // Failing test (inline)
                    if (preg_match('/^[\x{2717}\x{2715}\x{2718}\x{00d7}]\s+(.+?)\s+([\d.]+s)\s*$/u', $clean, $m)) {
                        $testCount++; $failCount++;
                        sendSSE('test', json_encode([
                            'name' => trim($m[1]), 'status' => 'fail', 'duration' => $m[2],
                            'total' => $testCount, 'passed' => $passCount, 'failed' => $failCount
                        ]));
                        continue;
                    }

                    // Failed test detail block
                    if (preg_match('/^FAILED\s+Tests\\\\Browser\\\\(\w+)\s+>\s+(.+?)/', $clean, $m)) {
                        sendSSE('fail-detail', json_encode([
                            'file' => $m[1], 'name' => trim($m[2]), 'line' => $clean
                        ]));
                        continue;
                    }

                    // Duration/summary
                    if (preg_match('/Duration:\s*([\d.]+s)/', $clean, $m)) {
                        sendSSE('duration', json_encode(['duration' => $m[1]]));
                    }
                    if (preg_match('/Tests:\s+/', $clean)) {
                        sendSSE('summary', json_encode(['line' => $clean]));
                    }
                }
            }

            if (!$status['running']) break;

            // Check if client disconnected
            if (connection_aborted()) {
                proc_terminate($proc);
                break;
            }

            usleep(200000); // 200ms
        }

        // Flush remaining buffer
        $out = stream_get_contents($pipes[1]);
        $err = stream_get_contents($pipes[2]);
        if ($out) file_put_contents($currentResult, $out, FILE_APPEND);
        if ($err) file_put_contents($currentResult, $err, FILE_APPEND);
        fclose($pipes[1]); fclose($pipes[2]);
        $exitCode = proc_close($proc);

        file_put_contents($pidFile, json_encode(['started' => 0, 'status' => 'completed']));

        sendSSE('done', json_encode([
            'total' => $testCount, 'passed' => $passCount, 'failed' => $failCount, 'exitCode' => $exitCode
        ]));
        break;

    // ==========================================
    // STOP
    // ==========================================
    case 'stop':
        header('Content-Type: application/json');
        killTests($isWindows);
        if (file_exists($pidFile)) {
            $d = json_decode(file_get_contents($pidFile), true);
            $d['status'] = 'stopped';
            file_put_contents($pidFile, json_encode($d));
        }
        echo json_encode(['success' => true]);
        break;

    // ==========================================
    // STATUS
    // ==========================================
    case 'status':
        header('Content-Type: application/json');
        $status = 'idle';
        if (file_exists($pidFile)) {
            $d = json_decode(file_get_contents($pidFile), true);
            $status = $d['status'] ?? 'unknown';
        }
        echo json_encode(['status' => $status]);
        break;

    // ==========================================
    // RESULTS
    // ==========================================
    case 'results':
        header('Content-Type: text/plain; charset=utf-8');
        clearstatcache(true, $currentResult);
        if (file_exists($currentResult)) readfile($currentResult);
        break;

    // ==========================================
    // HISTORY
    // ==========================================
    case 'history':
        header('Content-Type: application/json');
        $files = glob($historyDir . '/results_*.txt');
        $list = [];
        foreach ($files as $f) {
            $name = basename($f, '.txt');
            $ts = str_replace('results_', '', $name);
            $size = filesize($f);
            $tail = file_get_contents($f, false, null, max(0, $size - 1024));
            $clean = preg_replace('/\x1b\[[0-9;]*m/', '', $tail);
            preg_match('/(\d+)\s+failed/', $clean, $fm);
            preg_match('/(\d+)\s+passed/', $clean, $pm);
            $list[] = [
                'file' => basename($f),
                'date' => $ts,
                'size' => $size > 1024 ? round($size/1024).'KB' : $size.'B',
                'passed' => intval($pm[1] ?? 0),
                'failed' => intval($fm[1] ?? 0),
            ];
        }
        usort($list, function($a,$b) { return strcmp($b['file'], $a['file']); });
        echo json_encode($list);
        break;

    // ==========================================
    // LOAD HISTORY
    // ==========================================
    case 'load-history':
        header('Content-Type: text/plain; charset=utf-8');
        $file = basename($_GET['file'] ?? '');
        $path = $historyDir . '/' . $file;
        if ($file && file_exists($path) && strpos($file, 'results_') === 0) readfile($path);
        else echo '';
        break;

    // ==========================================
    // CLEAR
    // ==========================================
    case 'clear':
        header('Content-Type: application/json');
        if (file_exists($currentResult)) file_put_contents($currentResult, '');
        if (file_exists($pidFile)) unlink($pidFile);
        echo json_encode(['success' => true]);
        break;

    default:
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Unknown action']);
}

function sendSSE($event, $data) {
    echo "event: {$event}\ndata: {$data}\n\n";
    @ob_flush();
    @flush();
}

function killTests($isWindows) {
    if ($isWindows) {
        exec('taskkill /F /IM chromedriver-win.exe 2>NUL');
        exec('taskkill /F /IM chromedriver.exe 2>NUL');
        exec('wmic process where "commandline like \'%artisan dusk%\'" call terminate 2>NUL');
    } else {
        exec('pkill -f "artisan dusk" 2>/dev/null');
        exec('pkill -f chromedriver 2>/dev/null');
    }
}
