<?php
require_once __DIR__ . '/config.php';

function record_visit($pdo){
    $today = date('Y-m-d');
    $stmt = $pdo->prepare("INSERT INTO visits (visit_date, views) VALUES (?, 1) ON DUPLICATE KEY UPDATE views = views + 1");
    $stmt->execute([$today]);
}

function get_visit_stats_last_days($pdo, $days = 14){
    $stmt = $pdo->prepare("SELECT visit_date, views FROM visits WHERE visit_date >= DATE_SUB(CURDATE(), INTERVAL ? DAY) ORDER BY visit_date");
    $stmt->execute([$days]);
    return $stmt->fetchAll();
}

function check_remember_me($pdo){
    if(!isset($_SESSION['admin_id']) && !empty($_COOKIE['remember_token'])){
        $token = $_COOKIE['remember_token'];
        $stmt = $pdo->prepare("SELECT id, username FROM admins WHERE remember_token = ?");
        $stmt->execute([$token]);
        $u = $stmt->fetch();
        if($u){
            $_SESSION['admin_id'] = $u['id'];
            $_SESSION['admin_user'] = $u['username'];
        }
    }
}

function is_admin_logged_in(){
    return isset($_SESSION['admin_id']);
}
