<?php
require_once '../functions.php';
check_remember_me($pdo);
if (!is_admin_logged_in()) header('Location: login.php');

// Statistik 14 hari terakhir
$stats = get_visit_stats_last_days($pdo, 14);
$labels = array_column($stats, 'visit_date');
$values = array_column($stats, 'views');

// Ambil data cerita
$all = $pdo->query("SELECT * FROM stories ORDER BY created_at DESC")->fetchAll();
$pending = $pdo->query("SELECT * FROM stories WHERE approved = 0 ORDER BY created_at DESC")->fetchAll();

// Periksa password default
$stmt = $pdo->prepare("SELECT password FROM admins WHERE id = ?");
$stmt->execute([$_SESSION['admin_id']]);
$admin = $stmt->fetch();
$using_default = ($admin && $admin['password'] === 'admin');
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Admin Dashboard</title>
  <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
  <link href="../assets/css/style.css" rel="stylesheet">
  <link href="../assets/css/themes.css" rel="stylesheet">
  <script src="../assets/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    body {
      font-family: "Poppins", "Segoe UI", Roboto, sans-serif;
      transition: background 0.8s ease, color 0.6s ease;
      min-height: 100vh;
    }

    /* 🔹 Navbar */
    .navbar {
      backdrop-filter: blur(12px);
      background: rgba(33,37,41,0.85) !important;
      box-shadow: 0 2px 12px rgba(0,0,0,0.25);
      padding: 0.75rem 1rem;
    }
    .navbar-brand {
      font-weight: 700;
      font-size: 1.25rem;
      color: #fff !important;
      display: flex;
      align-items: center;
      gap: .5rem;
    }
    .navbar .btn {
      border-radius: 8px;
      font-weight: 500;
      transition: transform .2s ease, box-shadow .2s ease;
    }
    .navbar .btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 3px 10px rgba(0,0,0,0.15);
    }

    /* 🔹 Tombol kembali ke pengguna - fixed pojok kanan */
    .btn-return {
      position: fixed;
      top: 1rem;
      right: 1rem;
      background: linear-gradient(90deg, #0d6efd, #20c997);
      color: #fff !important;
      border: none;
      border-radius: 10px;
      padding: 8px 14px;
      z-index: 1050;
      font-size: 0.9rem;
      font-weight: 600;
      box-shadow: 0 3px 10px rgba(13,110,253,0.3);
      transition: all .25s ease;
    }
    .btn-return:hover {
      transform: translateY(-2px);
      box-shadow: 0 5px 16px rgba(13,110,253,0.4);
    }

    /* 🔹 Kartu & tabel */
    .card {
      border-radius: 14px;
      box-shadow: 0 3px 12px rgba(0,0,0,0.08);
      backdrop-filter: blur(6px);
      transition: transform .2s ease, box-shadow .2s ease;
    }
    .card:hover {
      transform: translateY(-3px);
      box-shadow: 0 6px 16px rgba(0,0,0,0.12);
    }
    table th {
      background-color: rgba(245, 245, 245, 0.85);
    }

    /* 🔹 Grafik kecil dan responsive */
    #visitsChart {
      height: 180px !important;
    }

    /* 🔹 Alert password default */
    .alert-warning {
      border-radius: 10px;
      box-shadow: 0 2px 6px rgba(255,193,7,0.3);
    }

    /* 🔹 Responsif mobile */
    @media (max-width: 768px) {
      .btn-return {
        top: .75rem;
        right: .75rem;
        padding: 6px 10px;
        font-size: .8rem;
      }
      .navbar-brand { font-size: 1.1rem; }
      #visitsChart { height: 140px !important; }
    }
  </style>
</head>
<body id="pageBody" class="default-theme">

<!-- 🔹 Tombol Kembali ke Pengguna -->
<a href="../index.php" class="btn-return">🏠 Kembali ke Pengguna</a>

<!-- 🔹 Navbar Admin -->
<nav class="navbar navbar-expand-lg navbar-dark sticky-top">
  <div class="container-fluid">
    <a class="navbar-brand" href="dashboard.php">⚙️ Admin Panel</a>
    <div class="ms-auto d-flex flex-wrap align-items-center gap-2">
      <a class="btn btn-outline-light btn-sm" href="change_password.php">🔑 Ubah Password</a>
      <a class="btn btn-outline-danger btn-sm" href="logout.php">🚪 Logout</a>
    </div>
  </div>
</nav>

<!-- 🔹 Konten -->
<div class="container my-4">
  <?php if ($using_default): ?>
    <div class="alert alert-warning shadow-sm">
      ⚠️ Anda masih memakai password default <strong>admin</strong> — segera ganti di 
      <a href="change_password.php" class="alert-link">Ubah Password</a>.
    </div>
  <?php endif; ?>

  <div class="row g-4">
    <!-- 🔸 Pending -->
    <div class="col-lg-8">
      <div class="card p-3 mb-4">
        <h5 class="mb-3">⏳ Pending Moderation</h5>
        <?php if (empty($pending)): ?>
          <div class="alert alert-secondary mb-0">Tidak ada cerita yang menunggu moderasi.</div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-bordered align-middle">
              <thead class="table-light">
                <tr>
                  <th>ID</th><th>Judul</th><th>Author</th><th>Waktu</th><th>Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($pending as $p): ?>
                <tr>
                  <td><?= $p['id'] ?></td>
                  <td><?= e($p['title']) ?></td>
                  <td><?= e($p['author']) ?></td>
                  <td><?= e($p['created_at']) ?></td>
                  <td class="d-flex flex-wrap gap-1">
                    <a class="btn btn-success btn-sm" href="approve_story.php?id=<?= $p['id'] ?>&action=approve">Setujui</a>
                    <a class="btn btn-warning btn-sm" href="edit_story.php?id=<?= $p['id'] ?>">Edit</a>
                    <a class="btn btn-danger btn-sm" href="delete_story.php?id=<?= $p['id'] ?>" onclick="return confirm('Hapus cerita ini?')">Hapus</a>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>

      <!-- 🔸 Semua Cerita -->
      <div class="card p-3">
        <h5 class="mb-3">📚 Semua Cerita</h5>
        <div class="table-responsive">
          <table class="table table-striped align-middle">
            <thead class="table-light">
              <tr><th>ID</th><th>Judul</th><th>Author</th><th>Approved</th><th>Aksi</th></tr>
            </thead>
            <tbody>
              <?php foreach ($all as $a): ?>
              <tr>
                <td><?= $a['id'] ?></td>
                <td><?= e($a['title']) ?></td>
                <td><?= e($a['author']) ?></td>
                <td><?= $a['approved'] ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-secondary">No</span>' ?></td>
                <td class="d-flex flex-wrap gap-1">
                  <a class="btn btn-primary btn-sm" href="../detail.php?id=<?= $a['id'] ?>" target="_blank">Lihat</a>
                  <?php if (!$a['approved']): ?>
                    <a class="btn btn-success btn-sm" href="approve_story.php?id=<?= $a['id'] ?>&action=approve">Approve</a>
                  <?php else: ?>
                    <a class="btn btn-secondary btn-sm" href="approve_story.php?id=<?= $a['id'] ?>&action=unapprove">Unapprove</a>
                  <?php endif; ?>
                  <a class="btn btn-warning btn-sm" href="edit_story.php?id=<?= $a['id'] ?>">Edit</a>
                  <a class="btn btn-danger btn-sm" href="delete_story.php?id=<?= $a['id'] ?>" onclick="return confirm('Hapus cerita ini?')">Hapus</a>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- 🔹 Statistik -->
    <div class="col-lg-4">
      <div class="card p-3">
        <h5 class="mb-3">📈 Statistik (14 Hari)</h5>
        <canvas id="visitsChart"></canvas>
      </div>
    </div>
  </div>
</div>

<!-- 🔹 Script Chart dan Tema -->
<script>
const labels = <?= json_encode($labels) ?>;
const values = <?= json_encode($values) ?>;

new Chart(document.getElementById('visitsChart').getContext('2d'), {
  type: 'line',
  data: {
    labels,
    datasets: [{
      label: 'Kunjungan',
      data: values,
      borderColor: '#0d6efd',
      backgroundColor: 'rgba(13,110,253,0.15)',
      borderWidth: 2,
      tension: 0.35,
      fill: true,
      pointRadius: 2.5
    }]
  },
  options: {
    scales: { y: { beginAtZero: true } },
    plugins: { legend: { display: false } },
    responsive: true,
    maintainAspectRatio: false
  }
});

// 🔹 Sinkronisasi tema utama
window.onload = () => {
  const saved = localStorage.getItem('theme') || 'default-theme';
  document.body.className = saved;
};
</script>
</body>
</html>
