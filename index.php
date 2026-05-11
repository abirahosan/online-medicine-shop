<?php
session_start();

require 'config/config.php';
require 'models.php';
require 'controllers.php';

$page = $_GET['page'] ?? 'login';

//logout
if ($page === 'logout') {
    $_SESSION = [];
    session_destroy();
    setcookie('remember_me', '', time() - 3600, '/');
    header('Location: index.php?page=login');
    exit;
}

//ajax
if ($page === 'ajax_orders') {
    header('Content-Type: application/json');
    if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
        http_response_code(403);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
    ordersCtrl($conn);
    exit;
}

//auth gate
$publicPages = ['login', 'register'];

if (in_array($page, $publicPages) && isset($_SESSION['user'])) {
    header('Location: index.php?page=dashboard');
    exit;
}

if (!in_array($page, $publicPages) && !isset($_SESSION['user'])) {
    header('Location: index.php?page=login');
    exit;
}

$adminPages = ['dashboard', 'categories', 'medicines', 'customers', 'orders', 'history'];
if (in_array($page, $adminPages) && $_SESSION['user']['role'] !== 'admin') {
    header('Location: index.php?page=login');
    exit;
}

switch ($page) {
    case 'login':      loginCtrl($conn);      break;
    case 'register':   registerCtrl($conn);   break;
    case 'dashboard':  dashboardCtrl($conn);  break;
    case 'categories': categoryCtrl($conn);   break;
    case 'medicines':  medicineCtrl($conn);   break;
    case 'customers':  customerCtrl($conn);   break;
    case 'orders':     ordersCtrl($conn);     break;
    case 'history':    historyCtrl($conn);    break;
    default:
        header('Location: index.php?page=login');
        exit;
}

mysqli_close($conn);
?>