<?php
require_once 'functions.php';
check_remember_me($pdo);

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM stories WHERE id = ?");
$stmt->execute([$id]);
$s = $stmt->fetch();
if (!$s) { echo 'Cerita tidak ditemukan.'; exit; }
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e($s['title']) ?> — CeritaSaya</title>

<link href="assets/css/bootstrap.min.css" rel="stylesheet">
<link href="assets/css/themes.css" rel="stylesheet">
<link href="assets/css/style.css" rel="stylesheet">

<style>
body {
  min-height: 100vh;
  background: url('assets/img/default-bg.jpg') center/cover fixed;
  font-family: "Poppins", system-ui, sans-serif;
  display: flex;
  flex-direction: column;
  align-items: center;
  transition: background 0.8s ease, color 0.4s ease;
  padding-bottom: 40px;
}

/* 🌸 Navbar */
.navbar {
  backdrop-filter: blur(10px);
  background: rgba(0, 123, 255, 0.85) !important;
  box-shadow: 0 4px 20px rgba(0,0,0,0.15);
  border-radius: 0 0 12px 12px;
}
.navbar-brand {
  font-weight: 700;
  font-size: 1.3rem;
  letter-spacing: 0.5px;
}
.navbar .btn {
  font-weight: 500;
  border-radius: 10px;
}

/* 🧾 Card Cerita */
.story-card {
  width: 100%;
  max-width: 900px;
  background: rgba(255, 255, 255, 0.92);
  border: none;
  border-radius: 20px;
  box-shadow: 0 10px 35px rgba(0, 0, 0, 0.12);
  overflow: hidden;
  animation: fadeIn 0.8s ease both;
  backdrop-filter: blur(8px);
}
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(25px); }
  to { opacity: 1; transform: translateY(0); }
}

/* 🖼️ Gambar Cerita */
.story-image {
  width: 100%;
  height: 380px;
  object-fit: cover;
}

/* ✍️ Isi */
.story-body {
  padding: 2rem;
}
.story-title {
  font-weight: 700;
  margin-bottom: 0.5rem;
}
.story-meta {
  font-size: 0.9rem;
  color: #6b7280;
  margin-bottom: 1.5rem;
}
.story-content {
  line-height: 1.8;
  font-size: 1.05rem;
  color: #374151;
  white-space: pre-line;
}

/* 🌗 Tema selaras */
.theme-dark body {
  background: url('assets/img/dark-bg.jpg') center/cover fixed;
  color: #f1f5f9;
}
.theme-dark .story-card {
  background: rgba(30, 32, 36, 0.9);
}
.theme-dark .story-content {
  color: #e5e7eb;
}
.theme-floral body {
  background: url('assets/img/floral-bg.jpg') center/cover fixed;
}
.theme-light body {
  background: url('assets/img/light-bg.jpg') center/cover fixed;
}

/* 📱 Responsif */
@media (max-width: 768px) {
  .story-image { height: 250px; }
  .story-body { padding: 1.5rem; }
}
</style>
</head>

<body class="theme-default">
<nav class="navbar navbar-expand-lg navbar-dark bg-primary w-100 sticky-top shadow-sm">
  <div class="container d-flex justify-content-between align-items-center">
    <a class="navbar-brand" href="index.php">Cerita Saya</a>
    <a class="btn btn-light btn-sm px-3" href="index.php">⬅ Kembali</a>
  </div>
</nav>

<main class="container my-4 d-flex justify-content-center">
  <div class="story-card">
    <?php if (!empty($s['image'])): ?>
      <img src="<?= e($s['image']) ?>" alt="Gambar Cerita" class="story-image">
    <?php endif; ?>

    <div class="story-body">
      <h2 class="story-title"><?= e($s['title']) ?></h2>
      <p class="story-meta">✍️ Oleh <strong><?= e($s['author']) ?></strong> • 🕓 <?= date('d M Y', strtotime($s['created_at'])) ?></p>
      <hr>
      <div class="story-content"><?= nl2br(e($s['content'])) ?></div>
    </div>
  </div>
</main>

<script src="assets/js/bootstrap.bundle.min.js"></script>
<script>
  // Terapkan tema terakhir dari localStorage
  window.onload = function() {
    const saved = localStorage.getItem('theme') || 'theme-default';
    document.body.className = saved;
  };
</script>
</body>
</html>
