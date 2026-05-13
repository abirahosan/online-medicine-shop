<?php
// ================================================================
// FRONT CONTROLLER - router
// ================================================================
session_start();

require 'config.php';
require 'models.php';
require 'controllers.php';

$page = $_GET['page'] ?? 'home';

/* ---------- Logout ---------- */
if ($page === 'logout') {
    logoutCtrl();
}

/* ---------- AJAX search endpoint ---------- */
if ($page === 'ajax') {
    ajaxSearchCtrl($conn);
    exit;
}

/* ---------- Public pages ---------- */
$publicPages = ['login', 'register'];

// Already logged in -> skip login/register
if (in_array($page, $publicPages) && isset($_SESSION['user'])) {
    header('Location: index.php?page=home');
    exit;
}

// Protected pages require login
if (!in_array($page, $publicPages) && !isset($_SESSION['user'])) {
    header('Location: index.php?page=login');
    exit;
}

/* ---------- Dispatch ---------- */
switch ($page) {
    case 'login':    loginCtrl($conn);    break;
    case 'register': registerCtrl($conn); break;
    case 'profile':  profileCtrl($conn);  break;
    case 'home':     homeCtrl($conn);     break;
    default:
        header('Location: index.php?page=home');
        exit;
}

mysqli_close($conn);
?>
