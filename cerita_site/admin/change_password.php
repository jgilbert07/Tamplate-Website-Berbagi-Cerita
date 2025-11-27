<?php
require_once '../functions.php';
if (!is_admin_logged_in()) header('Location: login.php');

$msg = '';
$alert_type = 'info';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cur = $_POST['current'] ?? '';
    $new = $_POST['new'] ?? '';
    $confirm = $_POST['confirm'] ?? '';

    $stmt = $pdo->prepare("SELECT password FROM admins WHERE id = ?");
    $stmt->execute([$_SESSION['admin_id']]);
    $u = $stmt->fetch();

    if (!$u) {
        $msg = 'User tidak ditemukan.';
        $alert_type = 'danger';
    } else {
        $stored = $u['password'];
        $ok = ($cur === $stored) || password_verify($cur, $stored);

        if (!$ok) {
            $msg = 'Password saat ini salah.';
            $alert_type = 'danger';
        } elseif ($new === '' || $new !== $confirm) {
            $msg = 'Password baru kosong atau tidak cocok.';
            $alert_type = 'warning';
        } else {
            $hash = password_hash($new, PASSWORD_DEFAULT);
            $pdo->prepare("UPDATE admins SET password = ? WHERE id = ?")->execute([$hash, $_SESSION['admin_id']]);
            $msg = '✅ Password berhasil diubah.';
            $alert_type = 'success';
        }
    }
}
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Ubah Password Admin</title>
<link href="../assets/css/bootstrap.min.css" rel="stylesheet">
<link href="../assets/css/themes.css" rel="stylesheet">
<link href="../assets/css/style.css" rel="stylesheet">

<style>
/* ✨ Layout Utama */
body {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 100vh;
  transition: background 1s ease, color .6s ease;
}

.change-card {
  max-width: 420px;
  width: 100%;
  backdrop-filter: blur(10px);
  background-color: rgba(255,255,255,0.85);
  border-radius: 16px;
  box-shadow: 0 8px 32px rgba(0,0,0,0.15);
  padding: 2rem;
  animation: fadeIn .8s ease-in-out;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(-15px); }
  to { opacity: 1; transform: translateY(0); }
}

h4 {
  font-weight: 700;
  margin-bottom: 1.5rem;
  text-align: center;
}

.form-control {
  border-radius: 8px;
}

.btn {
  border-radius: 8px;
  font-weight: 600;
  transition: all .3s ease;
}

.btn-primary {
  background: linear-gradient(135deg, #4e73df, #224abe);
  border: none;
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

.btn-secondary {
  background-color: #6c757d;
  border: none;
}

.alert {
  border-radius: 10px;
  font-size: 0.9rem;
  text-align: center;
}

/* 🌗 Tema Menyesuaikan */
.theme-dark .change-card {
  background-color: rgba(25,25,25,0.85);
  color: #f8f9fa;
}
.theme-floral .change-card {
  background-color: rgba(255,245,248,0.9);
}
.theme-light .change-card {
  background-color: rgba(255,255,255,0.9);
}

/* 🔘 Switch Tema */
.theme-switcher {
  position: absolute;
  top: 20px;
  right: 20px;
}
.theme-switcher button {
  border: none;
  width: 36px;
  height: 36px;
  border-radius: 50%;
  margin-left: 5px;
  cursor: pointer;
  transition: transform .3s ease;
}
.theme-switcher button:hover {
  transform: scale(1.1);
}
</style>
</head>

<div class="change-card">
  <h4>Ubah Password</h4>
  <?php if ($msg): ?>
    <div class="alert alert-<?= e($alert_type) ?>"><?= e($msg) ?></div>
  <?php endif; ?>

  <form method="post">
    <div class="mb-3">
      <label for="current" class="form-label">Password Saat Ini</label>
      <input type="password" name="current" id="current" class="form-control" required>
    </div>

    <div class="mb-3">
      <label for="new" class="form-label">Password Baru</label>
      <input type="password" name="new" id="new" class="form-control" required>
    </div>

    <div class="mb-3">
      <label for="confirm" class="form-label">Konfirmasi Password Baru</label>
      <input type="password" name="confirm" id="confirm" class="form-control" required>
    </div>

    <div class="d-flex justify-content-between">
      <a href="dashboard.php" class="btn btn-secondary">Kembali</a>
      <button class="btn btn-primary">Ubah</button>
    </div>
  </form>
</div>

</body>
</html>
