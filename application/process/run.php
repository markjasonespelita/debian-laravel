<?php
// Zyphor Command Center — background script runner (framework-free).
// Starts a script detached from the request, logs its output to a file,
// and lets the UI poll that log by byte offset. The log survives page
// reloads, so the terminal view can pick up exactly where it left off.
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

// Where your scripts live. Adjust to your install.
const SCRIPTS_DIR = '../backend';

// job => [script file, takes an argument?, needs root?]
// Root jobs run through `sudo -n`, so add a matching sudoers rule (or swap in pkexec).
const JOBS = [
    'upgrade'       => ['upgrade.sh',       false, true],
    'clean'         => ['clean.sh',         false, true],
    'info'          => ['info.php',          false, false],
    'doctor-fix'    => ['doctor-fix.sh',    false, true],
    'doctor-report' => ['doctor-report.sh', false, false],
    'doctor-scan'   => ['doctor-scan.php',   false, false],
    'packages'      => ['packages.php',      false, false],
    'search'        => ['search.php',        true,  false],
    'install'       => ['install.sh',       true,  true],
    'uninstall'     => ['uninstall.sh',     true,  true],
];

function out(array $data, int $status = 200)
{
    http_response_code($status);
    echo json_encode($data);
    exit;
}

// Local use only: this endpoint runs commands.
if (!in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true)) {
    out(['ok' => false, 'error' => 'Forbidden'], 403);
}

$home = getenv('HOME') ?: sys_get_temp_dir();
$dir  = $home . '/.config/zyphor-command-center-web/jobs';
if (!is_dir($dir) && !mkdir($dir, 0700, true)) {
    out(['ok' => false, 'error' => 'Cannot create job directory'], 500);
}

$action = $_REQUEST['action'] ?? '';

if ($action === 'ping') {
    out(['ok' => true]);
}

if ($action === 'start') {
    $job = $_POST['job'] ?? '';
    $arg = trim($_POST['arg'] ?? '');
    if (!isset(JOBS[$job])) {
        out(['ok' => false, 'error' => 'Unknown job'], 400);
    }
    [$script, $takesArg, $root] = JOBS[$job];
    if ($takesArg && !preg_match('/^[A-Za-z0-9._+:@\/-]{1,100}$/', $arg)) {
        out(['ok' => false, 'error' => 'Invalid package name'], 400);
    }

    $id   = bin2hex(random_bytes(6));
    $log  = "$dir/$id.log";
    $exit = "$dir/$id.exit";

    $inner = ($root ? 'sudo -n ' : '')
           . escapeshellarg(SCRIPTS_DIR . '/' . $script)
           . ($takesArg ? ' ' . escapeshellarg($arg) : '');
    $cmd = sprintf(
        'nohup sh -c %s > %s 2>&1 & echo $!',
        escapeshellarg($inner . '; echo $? > ' . escapeshellarg($exit)),
        escapeshellarg($log)
    );
    $pid = (int) shell_exec($cmd);
    file_put_contents("$dir/$id.pid", (string) $pid);
    out(['ok' => true, 'id' => $id]);
}

// poll and stop both need a valid run id
$id = $_REQUEST['id'] ?? '';
if (!preg_match('/^[a-f0-9]{12}$/', $id) || !is_file("$dir/$id.log")) {
    out(['ok' => false, 'error' => 'Unknown run'], 404);
}
$log  = "$dir/$id.log";
$exit = "$dir/$id.exit";

if ($action === 'stop') {
    $pid = (int) @file_get_contents("$dir/$id.pid");
    if ($pid > 1 && !is_file($exit)) {
        shell_exec('pkill -TERM -P ' . $pid . ' 2>/dev/null; kill -TERM ' . $pid . ' 2>/dev/null');
        file_put_contents($exit, '143');
    }
    out(['ok' => true]);
}

if ($action === 'poll') {
    $offset = max(0, (int) ($_GET['offset'] ?? 0));
    $done   = is_file($exit);          // read before the log so we never miss final output
    clearstatcache();
    $size = filesize($log);
    $text = '';
    if ($size > $offset) {
        $fh = fopen($log, 'rb');
        fseek($fh, $offset);
        $text = (string) fread($fh, min($size - $offset, 262144));
        fclose($fh);
        if (!$done) {                  // while running, only send complete lines
            $cut  = strrpos($text, "\n");
            $text = $cut === false ? '' : substr($text, 0, $cut + 1);
        }
    }
    $offset  += strlen($text);
    $finished = $done && $offset >= $size;

    $res = ['ok' => true, 'text' => $text, 'offset' => $offset, 'status' => 'running'];
    if ($finished) {
        $code = (int) trim((string) file_get_contents($exit));
        $res['code']   = $code;
        $res['status'] = $code === 0 ? 'done' : (in_array($code, [130, 137, 143], true) ? 'stopped' : 'failed');
    }
    out($res);
}

out(['ok' => false, 'error' => 'Unknown action'], 400);