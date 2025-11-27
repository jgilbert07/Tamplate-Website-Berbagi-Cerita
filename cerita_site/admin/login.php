<?php
require_once '../functions.php';

// Check remember cookie
check_remember_me($pdo);
if (is_admin_logged_in()) header('Location: dashboard.php');

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);

    $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
    $stmt->execute([$username]);
    $u = $stmt->fetch();

    if ($u) {
        $stored = $u['password'];
        if ($password === $stored || password_verify($password, $stored)) {
            $_SESSION['admin_id'] = $u['id'];
            $_SESSION['admin_user'] = $u['username'];

            if ($remember) {
                $token = bin2hex(random_bytes(16));
                setcookie('remember_token', $token, time() + 60 * 60 * 24 * 7, '/');
                $upd = $pdo->prepare("UPDATE admins SET remember_token = ? WHERE id = ?");
                $upd->execute([$token, $u['id']]);
            }

            header('Location: dashboard.php');
            exit;
        } else {
            $msg = '🔒 Password salah.';
        }
    } else {
        $msg = '👤 User tidak ditemukan.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Login Admin</title>
<link href="../assets/css/bootstrap.min.css" rel="stylesheet">
<link href="../assets/css/themes.css" rel="stylesheet">
<link href="../assets/css/style.css" rel="stylesheet">

<style>
/* ✨ Login Page Styling */
body {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 100vh;
  transition: background 1s ease, color .6s ease;
}

.login-card {
  max-width: 400px;
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

.login-card h3 {
  font-weight: 700;
  margin-bottom: 1.5rem;
  text-align: center;
}

.form-control {
  border-radius: 8px;
}

.btn-primary {
  background: linear-gradient(135deg, #4e73df, #224abe);
  border: none;
  border-radius: 8px;
  font-weight: 600;
  transition: all .3s ease;
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

/* 🌙 Tema gelap menyesuaikan */
.theme-dark .login-card {
  background-color: rgba(25,25,25,0.85);
  color: #f8f9fa;
}

.theme-floral .login-card {
  background-color: rgba(255, 245, 248, 0.9);
}

.theme-light .login-card {
  background-color: rgba(255,255,255,0.9);
}

/* Alert modern */
.alert {
  border-radius: 10px;
  font-size: 0.9rem;
  text-align: center;
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

<div class="login-card">
  <h3>Login Admin</h3>
  <?php if ($msg): ?>
    <div class="alert alert-danger"><?= e($msg) ?></div>
  <?php endif; ?>
  <form method="post" autocomplete="off">
    <div class="mb-3">
      <input class="form-control" name="username" placeholder="Username" required>
    </div>
    <div class="mb-3">
      <input class="form-control" type="password" name="password" placeholder="Password" required>
    </div>
    <div class="form-check mb-3">
      <input class="form-check-input" type="checkbox" name="remember" id="remember">
      <label for="remember" class="form-check-label">Ingat saya</label>
    </div>
    <button class="btn btn-primary w-100">Masuk</button>
  </form>
</div>

</body>
</html>
