<?php
require_once '../functions.php';
if (!is_admin_logged_in()) header('Location: login.php');

$id = (int)($_GET['id'] ?? 0);

// Proses update data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)$_POST['id'];
    $title = trim($_POST['title']);
    $excerpt = trim($_POST['excerpt']);
    $content = trim($_POST['content']);
    $author = trim($_POST['author']);
    $theme = trim($_POST['theme']);
    $approved = isset($_POST['approved']) ? 1 : 0;
    $imagePath = $_POST['existing_image'] ?? null;

    // Upload gambar baru jika ada
    if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $uploadDir = '../assets/uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        $f = $_FILES['image'];
        $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if ($f['error'] === UPLOAD_ERR_OK && in_array($ext, $allowed) && $f['size'] <= 4 * 1024 * 1024) {
            $safe = time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
            $target = $uploadDir . $safe;
            if (move_uploaded_file($f['tmp_name'], $target)) $imagePath = substr($target, 3);
        }
    }

    $pdo->prepare("UPDATE stories 
        SET title=?, excerpt=?, content=?, image=?, theme=?, author=?, approved=?, updated_at=NOW() 
        WHERE id=?")->execute([$title, $excerpt, $content, $imagePath, $theme, $author, $approved, $id]);

    header('Location: dashboard.php');
    exit;
}

// Ambil data cerita
$stmt = $pdo->prepare("SELECT * FROM stories WHERE id = ?");
$stmt->execute([$id]);
$s = $stmt->fetch();
if (!$s) { echo 'Cerita tidak ditemukan'; exit; }
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Edit Cerita</title>
<link href="../assets/css/bootstrap.min.css" rel="stylesheet">
<link href="../assets/css/themes.css" rel="stylesheet">
<style>
body {
  font-family: "Poppins", system-ui, sans-serif;
  min-height: 100vh;
  background: url('../assets/img/default-bg.jpg') center/cover fixed;
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 30px 10px;
  transition: background 0.8s ease;
}

/* Navbar */
.navbar {
  border-radius: 12px;
  margin-bottom: 25px;
}
.navbar-brand {
  font-weight: 700;
  letter-spacing: 0.5px;
}

/* Card utama */
.card {
  width: 100%;
  max-width: 820px;
  border: none;
  border-radius: 18px;
  background: rgba(255,255,255,0.93);
  box-shadow: 0 10px 35px rgba(0,0,0,0.1);
  padding: 2.2rem;
  backdrop-filter: blur(12px);
  animation: fadeIn 0.7s ease both;
}
@keyframes fadeIn {
  from {opacity:0; transform:translateY(20px);}
  to {opacity:1; transform:translateY(0);}
}

/* Input form */
.form-control, .form-select {
  border-radius: 10px;
  border: 1px solid #d1d5db;
  padding: .65rem .75rem;
  transition: all .3s ease;
}
.form-control:focus, .form-select:focus {
  border-color: #4f46e5;
  box-shadow: 0 0 0 3px rgba(79,70,229,.25);
}

/* Tombol */
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
.btn-secondary {
  border-radius: 10px;
  font-weight: 500;
  padding: .6rem 1.4rem;
}

/* Gambar */
.image-preview {
  max-width: 250px;
  border-radius: 10px;
  border: 1px solid #ccc;
  margin-top: 10px;
}

/* Tema selaras */
.theme-dark body {
  background: url('../assets/img/dark-bg.jpg') center/cover fixed;
  color: #f1f5f9;
}
.theme-dark .card {
  background: rgba(28,30,32,0.92);
}
.theme-dark .form-control, .theme-dark .form-select {
  background-color: rgba(38,40,43,0.85);
  color: #f8fafc;
}
.theme-floral body {
  background: url('../assets/img/floral-bg.jpg') center/cover fixed;
}
.theme-light body {
  background: url('../assets/img/light-bg.jpg') center/cover fixed;
}

@media(max-width:576px){
  .card {padding:1.5rem;}
}
</style>
</head>
<body class="theme-default">

<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm w-100">
  <div class="container">
    <a class="navbar-brand" href="dashboard.php">Admin Cerita Saya</a>
  </div>
</nav>

<div class="card">
  <h4 class="mb-4 text-center fw-bold">✏️ Edit Cerita</h4>
  <form method="post" enctype="multipart/form-data">
    <input type="hidden" name="id" value="<?= $s['id'] ?>">
    <input type="hidden" name="existing_image" value="<?= e($s['image']) ?>">

    <div class="mb-3">
      <label>Judul Cerita</label>
      <input class="form-control" name="title" value="<?= e($s['title']) ?>" required>
    </div>

    <div class="mb-3">
      <label>Deskripsi Singkat</label>
      <textarea class="form-control" name="excerpt" rows="2"><?= e($s['excerpt']) ?></textarea>
    </div>

    <div class="mb-3">
      <label>Isi Cerita</label>
      <textarea class="form-control" name="content" rows="8" required><?= e($s['content']) ?></textarea>
    </div>

    <div class="row g-3 mb-3">
      <div class="col-md-6">
        <label>Penulis</label>
        <input class="form-control" name="author" value="<?= e($s['author']) ?>">
      </div>
      <div class="col-md-6">
        <label>Tema</label>
        <select class="form-select" name="theme">
          <option value="default" <?= $s['theme']==='default'?'selected':'' ?>>Default</option>
          <option value="dark" <?= $s['theme']==='dark'?'selected':'' ?>>Dark</option>
          <option value="floral" <?= $s['theme']==='floral'?'selected':'' ?>>Floral</option>
          <option value="light" <?= $s['theme']==='light'?'selected':'' ?>>Light</option>
        </select>
      </div>
    </div>

    <div class="form-check mb-3">
      <input class="form-check-input" type="checkbox" name="approved" id="approved" <?= $s['approved'] ? 'checked' : '' ?>>
      <label class="form-check-label" for="approved">Disetujui</label>
    </div>

    <?php if (!empty($s['image'])): ?>
      <div class="mb-3">
        <label>Gambar Saat Ini</label>
        <div><img src="../<?= e($s['image']) ?>" class="image-preview" alt="Gambar Cerita"></div>
      </div>
    <?php endif; ?>

    <div class="mb-4">
      <label>Ganti Gambar</label>
      <input type="file" name="image" class="form-control" accept="image/*" onchange="previewImage(event)">
      <img id="preview" class="image-preview" style="display:none;">
    </div>

    <div class="text-end">
      <button class="btn btn-primary">💾 Simpan Perubahan</button>
      <a href="dashboard.php" class="btn btn-secondary">Kembali</a>
    </div>
  </form>
</div>

<script src="../assets/js/bootstrap.bundle.min.js"></script>
<script>
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

window.onload = function() {
  const saved = localStorage.getItem('theme') || 'theme-default';
  document.body.className = saved;
};
</script>
</body>
</html>
