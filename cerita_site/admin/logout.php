<?php
require_once '../functions.php';
setcookie('remember_token','',time()-3600,'/');
session_destroy();
header('Location: login.php'); exit;
