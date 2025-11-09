<?php
// functions.php
require_once __DIR__ . '/config.php';

session_start();

function load_data() {
    if (!file_exists(DATA_FILE)) {
        file_put_contents(DATA_FILE, json_encode(['videos' => []], JSON_PRETTY_PRINT));
    }
    $txt = file_get_contents(DATA_FILE);
    $arr = json_decode($txt, true);
    if (!is_array($arr)) $arr = ['videos' => []];
    if (!isset($arr['videos'])) $arr['videos'] = [];
    return $arr;
}

function save_data($data) {
    file_put_contents(DATA_FILE, json_encode($data, JSON_PRETTY_PRINT));
}

function sanitize_filename($name) {
    // remove anything dangerous
    $name = preg_replace('/[^A-Za-z0-9_\-\. ]/', '', $name);
    $name = trim($name);
    return $name;
}

function find_video($id) {
    $data = load_data();
    foreach ($data['videos'] as $v) {
        if ($v['id'] === $id) return $v;
    }
    return null;
}

function require_admin_login() {
    if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
        header('Location: admin.php');
        exit;
    }
}

// helper to compute hash for video password
function make_pass_hash($plain) {
    return password_hash($plain, PASSWORD_DEFAULT);
}

// verify video password
function verify_pass_hash($plain, $hash) {
    return password_verify($plain, $hash);
}