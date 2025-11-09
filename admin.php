<?php
// admin.php
require_once __DIR__ . '/functions.php';

$action = $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $pw = $_POST['pw'] ?? '';
    if ($pw === ADMIN_PASSWORD) {
        $_SESSION['is_admin'] = true;
        header('Location: admin.php');
        exit;
    } else {
        $err = "Password admin salah.";
    }
}

if (isset($_GET['logout'])) {
    unset($_SESSION['is_admin']);
    header('Location: admin.php');
    exit;
}

if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true) {
    // handle admin actions (upload, edit, delete)
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload'])) {
        if (!isset($_FILES['video']) || $_FILES['video']['error'] !== UPLOAD_ERR_OK) {
            $err = "Upload gagal.";
        } else {
            $name = sanitize_filename($_FILES['video']['name']);
            $tmp = $_FILES['video']['tmp_name'];
            $size = filesize($tmp);
            $ext = pathinfo($name, PATHINFO_EXTENSION);
            $allowed = ['mp4','webm','ogg','mkv','mov'];
            if (!in_array(strtolower($ext), $allowed)) {
                $err = "Format tidak didukung.";
            } else {
                $id = uniqid();
                $newFile = VIDEOS_DIR . '/' . $id . '.' . $ext;
                if (move_uploaded_file($tmp, $newFile)) {
                    $pw = $_POST['vpassword'] ?? '';
                    $hash = make_pass_hash($pw);
                    $data = load_data();
                    $data['videos'][] = [
                        'id' => $id,
                        'name' => $name,
                        'file' => basename($newFile),
                        'size' => $size,
                        'hash' => $hash
                    ];
                    save_data($data);
                    $msg = "Video berhasil diupload.";
                } else {
                    $err = "Gagal memindahkan file.";
                }
            }
        }
    }

    // edit password
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editpw'])) {
        $id = $_POST['id'] ?? '';
        $pw = $_POST['vpassword'] ?? '';
        $data = load_data();
        foreach ($data['videos'] as &$vv) {
            if ($vv['id'] === $id) {
                $vv['hash'] = make_pass_hash($pw);
                save_data($data);
                $msg = "Password diperbarui.";
                break;
            }
        }
    }

    // delete
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
        $id = $_POST['id'] ?? '';
        $data = load_data();
        foreach ($data['videos'] as $k => $vv) {
            if ($vv['id'] === $id) {
                $filepath = VIDEOS_DIR . '/' . $vv['file'];
                if (file_exists($filepath)) unlink($filepath);
                unset($data['videos'][$k]);
                save_data($data);
                $msg = "Video dihapus.";
                break;
            }
        }
    }

    // reload data for dashboard
    $data = load_data();
    $videos = $data['videos'];
    ?>

    <!doctype html>
    <html lang="id">
    <head>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width,initial-scale=1">
      <title>Admin — Protected Video List</title>
      <link rel="stylesheet" href="style.css">
    </head>
    <body>
      <header class="navbar">
        <h1>Admin Panel</h1>
        <div class="actions">
          <a class="btn ghost" href="index.php">🏠 Lihat situs</a>
          <a class="btn danger" href="admin.php?logout=1">🔒 Logout</a>
        </div>
      </header>

      <main>
        <section style="max-width:900px;margin:0 auto">
          <?php if(isset($err)): ?><div class="alert error"><?php echo htmlspecialchars($err); ?></div><?php endif; ?>
          <?php if(isset($msg)): ?><div class="alert success"><?php echo htmlspecialchars($msg); ?></div><?php endif; ?>

          <h2>Upload video baru</h2>
          <form method="post" enctype="multipart/form-data">
            <div class="form-row">
              <label>File video</label>
              <input type="file" name="video" accept="video/*" required>
            </div>
            <div class="form-row">
              <label>Password untuk video</label>
              <input type="text" name="vpassword" placeholder="Set password untuk video" required>
            </div>
            <div class="form-row">
              <button type="submit" name="upload" class="btn">Upload</button>
            </div>
          </form>

          <hr>

          <h2>Daftar video</h2>
          <?php if (empty($videos)): ?>
            <p>Tidak ada video.</p>
          <?php else: ?>
            <table class="admin-table">
              <thead><tr><th>Preview</th><th>Nama</th><th>Ukuran</th><th>Aksi</th></tr></thead>
              <tbody>
              <?php foreach ($videos as $vv): ?>
                <tr>
                  <td style="width:160px"><video src="stream.php?id=<?php echo $vv['id']; ?>&preview=1" muted width="150"></video></td>
                  <td><?php echo htmlspecialchars($vv['name']); ?></td>
                  <td><?php echo round($vv['size']/1024/1024,2); ?> MB</td>
                  <td>
                    <form method="post" style="display:inline-block">
                      <input type="hidden" name="id" value="<?php echo htmlspecialchars($vv['id']); ?>">
                      <input type="text" name="vpassword" placeholder="Masukkan password baru" required>
                      <button type="submit" name="editpw" class="btn">Ubah PW</button>
                    </form>
                    <form method="post" style="display:inline-block" onsubmit="return confirm('Hapus video?')">
                      <input type="hidden" name="id" value="<?php echo htmlspecialchars($vv['id']); ?>">
                      <button type="submit" name="delete" class="btn danger">Hapus</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
          <?php endif; ?>

        </section>
      </main>
    </body>
    </html>

    <?php
    exit;
}

// not logged in: show login
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Admin Login</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <header class="navbar">
    <h1>Admin Login</h1>
    <div class="actions">
      <a class="btn glass" href="index.php">🏠 Kembali</a>
    </div>
  </header>

  <main>
    <section style="max-width:420px;margin:20px auto">
      <?php if(isset($err)): ?><div class="alert error"><?php echo htmlspecialchars($err); ?></div><?php endif; ?>

      <form method="post">
        <div class="form-row">
          <label>Password Admin</label>
          <input type="password" name="pw" required>
        </div>
        <div class="form-row">
          <button type="submit" name="login" class="btn">Login</button>
        </div>
      </form>
      <p class="note">Catatan: ganti password admin di file <code>config.php</code>.</p>
    </section>
  </main>
</body>
</html>