<?php
require_once '../functions.php';
if(!is_admin_logged_in()) header('Location: login.php');
$id = (int)($_GET['id'] ?? 0);
if($id){ $pdo->prepare("DELETE FROM stories WHERE id = ?")->execute([$id]); }
header('Location: dashboard.php'); exit;
