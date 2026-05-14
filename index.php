<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
// FRONT CONTROLLER (router)
session_start();

require 'config.php';
require 'models.php';
require 'controllers.php';

$page = $_GET['page'] ?? 'login';

if (!isset($_GET['page']) && isset($_SESSION['user_id'])) {
    $page = 'home';
}

//Logout
if ($page === 'logout') {
    $_SESSION = [];
    session_destroy();
    setcookie('remember_email', '', time() - 3600, '/');
    header('Location: index.php?page=login');
    exit;
}

//AJAX search endpoint 
if ($page === 'ajax_search') {
    ajaxSearchCtrl($conn);
    exit;
}

//Auth gates 
$publicPages = ['login', 'register'];

// Already logged in -> skip login/register
if (in_array($page, $publicPages) && isset($_SESSION['user_id'])) {
    header('Location: index.php?page=home');
    exit;
}

// Protected pages require login
$protectedPages = ['profile'];
if (in_array($page, $protectedPages) && !isset($_SESSION['user_id'])) {
    header('Location: index.php?page=login');
    exit;
}

// Admin gate
if ($page === 'admin' && (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin')) {
    header('Location: index.php?page=login');
    exit;
}

// Dispatch 
switch ($page) {
    case 'register':    registerCtrl($conn);    break;
    case 'login':       loginCtrl($conn);       break;
    case 'profile':     profileCtrl($conn);     break;
    case 'home':        homeCtrl($conn);        break;
    case 'categories':  categoriesCtrl($conn);  break;
    default:
        header('Location: index.php?page=home');
        exit;
}

mysqli_close($conn);
?>