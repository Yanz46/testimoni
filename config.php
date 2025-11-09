<?php
// config.php
// UBAH admin password sebelum dipakai online!
// Isi dengan password admin yang ingin kamu pakai.
define('ADMIN_PASSWORD', 'admin123'); // change this to your own password
define('DATA_FILE', __DIR__ . '/data.json');
define('VIDEOS_DIR', __DIR__ . '/videos');

// Ensure videos dir exists
if (!is_dir(VIDEOS_DIR)) {
    mkdir(VIDEOS_DIR, 0755, true);
}