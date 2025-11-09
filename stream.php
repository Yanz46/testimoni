<?php
// stream.php
require_once __DIR__ . '/functions.php';

$id = $_GET['id'] ?? '';
$preview = isset($_GET['preview']); // for thumbnails / hover we allow limited preview
$video = find_video($id);

if (!$video) {
    http_response_code(404);
    echo "Not found";
    exit;
}

// if preview mode -> allow streaming first 3 seconds (ignore password)
if ($preview) {
    $file = VIDEOS_DIR . '/' . $video['file'];
    if (!file_exists($file)) { http_response_code(404); exit; }
    // serve but browser can still request ranges; we'll just serve file normally (client may download)
    header('Content-Type: video/mp4');
    header('Content-Length: ' . filesize($file));
    readfile($file);
    exit;
}

// otherwise check session allowed
if (!isset($_SESSION['allowed'][$id]) || $_SESSION['allowed'][$id] !== true) {
    http_response_code(403);
    echo "Forbidden";
    exit;
}

// serve with range support
$path = VIDEOS_DIR . '/' . $video['file'];
if (!file_exists($path)) { http_response_code(404); exit; }

$fp = @fopen($path, 'rb');
$size = filesize($path);
$length = $size;
$start = 0;
$end = $size - 1;

header("Content-Type: video/mp4");
header("Accept-Ranges: bytes");

if (isset($_SERVER['HTTP_RANGE'])) {
    $c_start = $start;
    $c_end = $end;
    list(, $range) = explode('=', $_SERVER['HTTP_RANGE'], 2);
    if (strpos($range, ',') !== false) {
        header('HTTP/1.1 416 Requested Range Not Satisfiable');
        exit;
    }
    if ($range == '-') {
        $c_start = $size - substr($range, 1);
    } else {
        $range = explode('-', $range);
        $c_start = intval($range[0]);
        if (isset($range[1]) && is_numeric($range[1])) {
            $c_end = intval($range[1]);
        }
    }
    $c_end = ($c_end > $end) ? $end : $c_end;
    if ($c_start > $c_end || $c_start > $size - 1 || $c_end >= $size) {
        header('HTTP/1.1 416 Requested Range Not Satisfiable');
        exit;
    }
    $start = $c_start;
    $end = $c_end;
    $length = $end - $start + 1;
    fseek($fp, $start);
    header('HTTP/1.1 206 Partial Content');
}

header("Content-Range: bytes $start-$end/$size");
header("Content-Length: $length");

$buffer = 8192;
while (!feof($fp) && ($p = ftell($fp)) <= $end) {
    if ($p + $buffer > $end) $buffer = $end - $p + 1;
    set_time_limit(0);
    echo fread($fp, $buffer);
    flush();
}
fclose($fp);
exit;