<?php
require_once '../functions.php';
if(!is_admin_logged_in()) header('Location: login.php');
$id = (int)($_GET['id'] ?? 0);
$action = $_GET['action'] ?? 'approve';
if($id){
    if($action==='approve') $pdo->prepare("UPDATE stories SET approved = 1 WHERE id = ?")->execute([$id]);
    else $pdo->prepare("UPDATE stories SET approved = 0 WHERE id = ?")->execute([$id]);
}
header('Location: dashboard.php'); exit;
