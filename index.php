<?php
session_start();

require 'config/config.php';
require 'models.php';
require 'controllers.php';

$page = $_GET['page'] ?? 'login';


if ($page === 'logout') {
    $_SESSION = [];
    session_destroy();
    header('Location: index.php?page=login');
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