<?php 
require_once 'functions.php';
check_remember_me($pdo);
record_visit($pdo);

$stmt = $pdo->prepare("SELECT id,title,excerpt,author,created_at,image,theme FROM stories WHERE approved = 1 ORDER BY created_at DESC");
$stmt->execute();
$stories = $stmt->fetchAll();
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>CeritaSaya - Kisah Inspiratif</title>
  <link href="assets/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/css/themes.css" rel="stylesheet">

  <style>
    /* --- Umum --- */
    body {
      transition: background 0.6s ease, color 0.4s ease;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }

    .navbar {
      transition: background-color 0.5s ease, box-shadow 0.3s ease;
      box-shadow: 0 2px 10px rgba(0,0,0,0.15);
    }

    .navbar-brand {
      font-weight: 700;
      letter-spacing: 0.5px;
    }

    /* --- Kartu Cerita --- */
    .card {
      border: none;
      border-radius: 15px;
      overflow: hidden;
      box-shadow: 0 4px 15px rgba(0,0,0,0.15);
      transition: transform 0.25s ease, box-shadow 0.25s ease;
      background-color: rgba(255,255,255,0.9);
    }

    .card:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 25px rgba(0,0,0,0.25);
    }

    .card img {
      height: 200px;
      object-fit: cover;
    }

    .card-title {
      font-weight: 600;
    }

    /* --- Tombol Dinamis Berdasarkan Tema --- */
    .btn-primary {
      transition: background-color 0.4s ease, color 0.4s ease, border 0.4s ease;
    }

    body.default-theme .btn-primary {
      background-color: #007bff;
      border: none;
      color: #fff;
    }

    body.dark-theme .btn-primary {
      background-color: #706fd3;
      border: none;
      color: #fff;
    }

    body.light-theme .btn-primary {
      background-color: #6c63ff;
      border: none;
      color: #fff;
    }

    body.floral-theme .btn-primary {
      background-color: #a52a5a;
      border: none;
      color: #fff;
    }

    /* --- Warna Teks Kontras Dinamis --- */
    body.dark-theme h2, 
    body.dark-theme p, 
    body.dark-theme .card-title, 
    body.dark-theme .card-text {
      color: #f1f1f1 !important;
    }

    body.floral-theme h2, 
    body.floral-theme p, 
    body.floral-theme .card-title {
      color: #2b2b2b;
    }

    body.light-theme h2, 
    body.light-theme p, 
    body.light-theme .card-title {
      color: #222;
    }

    /* --- Footer --- */
    footer {
      text-align: center;
      padding: 20px;
      margin-top: auto;
      font-size: 0.9rem;
      opacity: 0.8;
    }

    /* --- Selector Tema --- */
    #themeSelector {
      min-width: 150px;
    }
  </style>
</head>
<body id="pageBody" class="default-theme">

<!-- ===== Navbar ===== -->
<nav class="navbar navbar-expand-lg navbar-dark">
  <div class="container">
    <a class="navbar-brand" href="index.php">CeritaSaya</a>
    <div class="d-flex align-items-center gap-2">
      <select id="themeSelector" class="form-select form-select-sm rounded-pill">
        <option value="default-theme">Default</option>
        <option value="dark-theme">Dark</option>
        <option value="light-theme">Light</option>
        <option value="floral-theme">Floral</option>
      </select>
      <a class="btn btn-light btn-sm rounded-pill" href="submit.php">✍️ Kirim Cerita</a>
      <a class="btn btn-outline-light btn-sm rounded-pill" href="admin/login.php">Admin</a>
    </div>
  </div>
</nav>

<!-- ===== Konten ===== -->
<div class="container my-5">
  <div class="text-center mb-4">
    <h2 class="fw-bold">Cerita Terbaru</h2>
    <p class="text-muted">Kumpulan kisah inspiratif yang telah disetujui admin</p>
  </div>

  <div class="row g-4">
    <?php if(empty($stories)): ?>
      <div class="col-12">
        <div class="alert alert-info text-center">Belum ada cerita yang disetujui.</div>
      </div>
    <?php endif; ?>

    <?php foreach($stories as $s): ?>
      <div class="col-md-6 col-lg-4">
        <div class="card h-100 story-card" data-theme="<?= e($s['theme']) ?>">
          <?php if(!empty($s['image'])): ?>
            <img src="<?= e($s['image']) ?>" class="card-img-top" alt="<?= e($s['title']) ?>">
          <?php endif; ?>
          <div class="card-body d-flex flex-column">
            <h5 class="card-title"><?= e($s['title']) ?></h5>
            <p class="small text-muted mb-2"><?= e($s['author']) ?> • <?= date('d M Y', strtotime($s['created_at'])) ?></p>
            <p class="card-text flex-grow-1"><?= e($s['excerpt']) ?></p>
            <div class="mt-3">
              <a href="detail.php?id=<?= $s['id'] ?>" class="btn btn-primary w-100 rounded-pill">Baca Selengkapnya</a>
            </div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<!-- ===== JS Tema Dinamis ===== -->
<script>
  const themeSelector = document.getElementById('themeSelector');
  const body = document.getElementById('pageBody');

  function setTheme(theme) {
    body.className = theme;
    localStorage.setItem('theme', theme);
  }

  // Saat halaman dimuat, ambil tema tersimpan
  window.onload = () => {
    const saved = localStorage.getItem('theme') || 'default-theme';
    setTheme(saved);
    themeSelector.value = saved;
  };

  // Ganti tema saat dipilih
  themeSelector.addEventListener('change', (e) => setTheme(e.target.value));
</script>

<script src="assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
