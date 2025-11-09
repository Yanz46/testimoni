<?php
// verify.php
require_once __DIR__ . '/functions.php';

header('Content-Type: application/json');

$id = $_POST['id'] ?? '';
$pw = $_POST['password'] ?? '';

if (!$id || !$pw) {
    echo json_encode(['ok' => false, 'msg' => 'Invalid request']);
    exit;
}

$v = find_video($id);
if (!$v) {
    echo json_encode(['ok' => false, 'msg' => 'Video tidak ditemukan']);
    exit;
}

if (verify_pass_hash($pw, $v['hash'])) {
    // allow for this session
    if (!isset($_SESSION['allowed'])) $_SESSION['allowed'] = [];
    $_SESSION['allowed'][$id] = true;
    echo json_encode(['ok' => true]);
} else {
    echo json_encode(['ok' => false, 'msg' => 'Password salah']);
}