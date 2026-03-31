<?php
set_time_limit(0);
ignore_user_abort(true);
chdir("C:\\Users\\Jadallah\\Downloads\\PalestineCreativeHub (4)\\PalestineCreativeHub");
$outFile = "C:\\Users\\Jadallah\\Downloads\\PalestineCreativeHub (4)\\PalestineCreativeHub\\tests\\Browser/test-results-latest.txt";
file_put_contents($outFile, "");

$descriptors = [
    0 => ["pipe", "r"],
    1 => ["pipe", "w"],
    2 => ["pipe", "w"],
];

$proc = proc_open("php artisan dusk", $descriptors, $pipes, "C:\\Users\\Jadallah\\Downloads\\PalestineCreativeHub (4)\\PalestineCreativeHub");
if (!is_resource($proc)) {
    file_put_contents($outFile, "ERROR: Could not start php artisan dusk\n");
    exit(1);
}

fclose($pipes[0]);
stream_set_blocking($pipes[1], false);
stream_set_blocking($pipes[2], false);

while (true) {
    $s = proc_get_status($proc);
    $o = stream_get_contents($pipes[1]);
    $e = stream_get_contents($pipes[2]);
    if ($o !== '' && $o !== false) file_put_contents($outFile, $o, FILE_APPEND);
    if ($e !== '' && $e !== false) file_put_contents($outFile, $e, FILE_APPEND);
    if (!$s['running']) break;
    usleep(200000);
}
// Flush remaining
$o = stream_get_contents($pipes[1]);
$e = stream_get_contents($pipes[2]);
if ($o) file_put_contents($outFile, $o, FILE_APPEND);
if ($e) file_put_contents($outFile, $e, FILE_APPEND);
fclose($pipes[1]);
fclose($pipes[2]);
proc_close($proc);