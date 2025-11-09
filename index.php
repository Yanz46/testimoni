<?php
// index.php
require_once __DIR__ . '/functions.php';
$data = load_data();
$videos = $data['videos'];
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Protected Video List</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <header class="navbar">
    <h1>🎬 Protected Video List</h1>
    <div class="actions">
      <a class="btn glass" href="admin.php">🔧 Admin</a>
    </div>
  </header>

  <main>
    <section id="grid" class="video-grid">
      <?php if (empty($videos)): ?>
        <p style="grid-column:1/-1;text-align:center;opacity:0.6;">Belum ada video.</p>
      <?php else: ?>
        <?php foreach ($videos as $v): ?>
          <div class="card">
            <div class="thumb">
              <!-- show placeholder; actual preview via video element for hover -->
              <video src="stream.php?id=<?php echo htmlspecialchars($v['id']); ?>&preview=1" muted playsinline></video>
            </div>
            <div class="card-info">
              <h3><?php echo htmlspecialchars($v['name']); ?></h3>
              <p><?php echo round($v['size'] / 1024 / 1024, 2); ?> MB</p>
              <div class="card-actions">
                <button class="icon-btn" onclick="openPwModal('<?php echo $v['id']; ?>')">🔒 Buka</button>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </section>

    <p class="note">⚠️ Video dilindungi oleh password. Masukkan password untuk menonton. Semua file disimpan di server.</p>
  </main>

  <!-- password modal -->
  <div id="pwModal" class="modal hidden">
    <div class="modal-content glass">
      <h2>Masukkan Password</h2>
      <input id="pwInput" type="password" placeholder="Password..." />
      <div class="modal-buttons">
        <button id="pwCancel" class="btn ghost">Batal</button>
        <button id="pwSubmit" class="btn">Buka</button>
      </div>
    </div>
  </div>

  <!-- player modal -->
  <div id="playerModal" class="modal hidden">
    <div class="modal-content glass video-player">
      <div class="modal-header">
        <h2 id="playerTitle"></h2>
        <button id="playerClose" class="btn ghost">✖</button>
      </div>
      <video id="player" controls></video>
    </div>
  </div>

  <script src="script.js"></script>
</body>
</html>