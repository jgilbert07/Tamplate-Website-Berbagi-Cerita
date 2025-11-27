<?php
require_once 'functions.php';
check_remember_me($pdo);

$errors = []; 
$success = false; 
$pending = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $excerpt = trim($_POST['excerpt'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $author = trim($_POST['author'] ?? 'Anonim');
    $theme = trim($_POST['theme'] ?? 'default');

    if ($title === '' || $content === '') $errors[] = 'Judul dan isi wajib diisi.';

    $imagePath = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $uploadDir = 'assets/uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $f = $_FILES['image'];
        $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if ($f['error'] !== UPLOAD_ERR_OK) $errors[] = 'Gagal mengunggah file.';
        elseif ($f['size'] > 4 * 1024 * 1024) $errors[] = 'Ukuran file maksimal 4MB.';
        elseif (!in_array($ext, $allowed)) $errors[] = 'Format gambar tidak diizinkan.';
        else {
            $safe = time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
            $target = $uploadDir . $safe;
            if (move_uploaded_file($f['tmp_name'], $target)) $imagePath = $target;
            else $errors[] = 'Gagal memindahkan file.';
        }
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO stories (title, excerpt, content, image, theme, author, approved) VALUES (?, ?, ?, ?, ?, ?, 0)");
        $stmt->execute([$title, $excerpt, $content, $imagePath, $theme, $author]);
        $success = true;
        $pending = true;
    }
}
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Kirim Cerita</title>
<link href="assets/css/bootstrap.min.css" rel="stylesheet">
<link href="assets/css/themes.css" rel="stylesheet">
<link href="assets/css/style.css" rel="stylesheet">

<style>
body {
  min-height: 100vh;
  background-size: cover;
  background-position: center;
  font-family: "Poppins", system-ui, sans-serif;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: start;
  padding: 30px 10px;
  transition: background 0.8s ease, color 0.5s ease;
}

/* 🌸 Card utama */
.card {
  width: 100%;
  max-width: 720px;
  border: none;
  border-radius: 18px;
  backdrop-filter: blur(10px);
  background-color: rgba(255,255,255,0.92);
  box-shadow: 0 10px 30px rgba(0,0,0,0.15);
  padding: 2.2rem;
  animation: fadeIn 0.8s ease both;
}
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(20px);}
  to { opacity: 1; transform: translateY(0);}
}

h4 {
  font-weight: 700;
  text-align: center;
  margin-bottom: 1.5rem;
}

/* 🧱 Form */
label { font-weight: 600; margin-bottom: .4rem; }
.form-control, .form-select {
  border-radius: 10px;
  border: 1px solid #d1d5db;
  padding: .65rem .75rem;
  transition: all .3s ease;
}
.form-control:focus, .form-select:focus {
  border-color: #4f46e5;
  box-shadow: 0 0 0 3px rgba(79,70,229,.2);
}

/* 🧭 Button */
.btn-primary {
  background: linear-gradient(135deg, #4f46e5, #3b82f6);
  border: none;
  border-radius: 10px;
  font-weight: 600;
  padding: .6rem 1.4rem;
  transition: transform .2s ease, box-shadow .3s ease;
}
.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(79,70,229,0.3);
}

/* 📸 Preview gambar */
.image-preview {
  width: 100%;
  max-height: 230px;
  object-fit: cover;
  border-radius: 10px;
  display: none;
  margin-top: 10px;
  border: 1px solid #e2e8f0;
}

/* 🎨 Tema selaras */
.theme-dark .card {
  background-color: rgba(28,30,32,0.9);
  color: #f1f5f9;
}
.theme-dark .form-control, .theme-dark .form-select {
  background-color: rgba(38,40,43,0.85);
  color: #f8fafc;
  border-color: #555;
}
.theme-dark .btn-primary {
  background: linear-gradient(135deg, #6366f1, #2563eb);
}
.theme-dark body {
  background: url('assets/img/dark-bg.jpg') center/cover fixed;
}
.theme-floral body {
  background: url('assets/img/floral-bg.jpg') center/cover fixed;
}
.theme-light body {
  background: url('assets/img/light-bg.jpg') center/cover fixed;
}
.theme-default body {
  background: url('assets/img/default-bg.jpg') center/cover fixed;
}

/* 🔔 Alert */
.alert {
  border-radius: 12px;
  font-size: .95rem;
}

/* 📱 Responsif */
@media(max-width:576px){
  .card{padding:1.5rem;}
}
</style>
</head>

<body class="theme-default">
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm w-100">
  <div class="container">
    <a class="navbar-brand fw-bold" href="index.php">Cerita Saya</a>
  </div>
</nav>

<div class="card mt-4">
  <h4>📝 Kirim Cerita Kamu</h4>

  <?php if ($success && $pending): ?>
    <div class="alert alert-success text-center">
      <strong>Berhasil!</strong> Ceritamu telah dikirim dan sedang menunggu moderasi admin.
    </div>
  <?php endif; ?>

  <?php foreach ($errors as $err): ?>
    <div class="alert alert-danger"><?= e($err) ?></div>
  <?php endforeach; ?>

  <form method="post" enctype="multipart/form-data">
    <div class="mb-3">
      <label>Judul Cerita</label>
      <input class="form-control" name="title" placeholder="Masukkan judul menarik..." required>
    </div>

    <div class="mb-3">
      <label>Deskripsi Singkat</label>
      <textarea class="form-control" name="excerpt" rows="2" placeholder="Tuliskan ringkasan singkat..."></textarea>
    </div>

    <div class="mb-3">
      <label>Isi Cerita</label>
      <textarea class="form-control" name="content" rows="8" placeholder="Tuliskan cerita kamu di sini..." required></textarea>
    </div>

    <div class="mb-3">
      <label>Gambar Cerita (opsional)</label>
      <input type="file" name="image" class="form-control" accept="image/*" onchange="previewImage(event)">
      <img id="preview" class="image-preview" alt="Preview Gambar">
    </div>

    <div class="row g-3 mb-3">
      <div class="col-md-6">
        <label>Nama Penulis</label>
        <input class="form-control" name="author" placeholder="Anonim">
      </div>
      <div class="col-md-6">
        <label>Tema</label>
        <select name="theme" class="form-select">
          <option value="default">Default</option>
          <option value="dark">Dark</option>
          <option value="floral">Floral</option>
          <option value="light">Light</option>
        </select>
      </div>
    </div>

    <div class="text-end">
      <button class="btn btn-primary px-4">Kirim Cerita</button>
    </div>
  </form>
</div>

<script src="assets/js/bootstrap.bundle.min.js"></script>
<script>
function setTheme(theme) {
  document.body.className = theme;
  localStorage.setItem('theme', theme);
}

window.onload = function() {
  const saved = localStorage.getItem('theme') || 'theme-default';
  document.body.className = saved;
};

function previewImage(e) {
  const img = document.getElementById('preview');
  const file = e.target.files[0];
  if (file) {
    img.style.display = 'block';
    img.src = URL.createObjectURL(file);
  } else {
    img.style.display = 'none';
  }
}
</script>
</body>
</html>
