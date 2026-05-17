<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();

require 'config/config.php';
require 'models.php';
require 'controllers.php';

$page = $_GET['page'] ?? 'login';

// Default page for logged in users
if (!isset($_GET['page']) && isset($_SESSION['user_id'])) {
    $page = 'home';
}

// LOGOUT 
if ($page === 'logout') {
    $_SESSION = [];
    session_destroy();
    setcookie('remember_email', '', time() - 3600, '/');
    header('Location: index.php?page=login');
    exit;
}

//AJAX SEARCH (Task 1) 
if ($page === 'ajax_search') {
    ajaxSearchCtrl($conn);
    exit;
}

// AUTH GATE 
$publicPages = ['login', 'register'];

// Already logged in → skip login/register
if (in_array($page, $publicPages) && isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header('Location: index.php?page=dashboard');
    } else {
        header('Location: index.php?page=home');
    }
    exit;
}

// Protected pages require login
$protectedPages = ['profile', 'home', 'dashboard', 'categories', 'medicines', 'customers', 'orders', 'history'];
if (in_array($page, $protectedPages) && !isset($_SESSION['user_id'])) {
    header('Location: index.php?page=login');
    exit;
}

// Admin only pages
$adminPages = ['dashboard', 'categories', 'medicines', 'customers', 'orders', 'history'];
if (in_array($page, $adminPages) && (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin')) {
    header('Location: index.php?page=login');
    exit;
}

// DISPATCH 
switch ($page) {
    // Task 1 routes
    case 'login':        loginCtrl($conn);            break;
    case 'register':     registerCtrl($conn);         break;
    case 'profile':      profileCtrl($conn);          break;
    case 'home':         homeCtrl($conn);             break;
    case 'browse':       categoriesBrowseCtrl($conn); break;
    // Task 2 routes
    case 'dashboard':    dashboardCtrl($conn);        break;
    case 'categories':   categoryCtrl($conn);         break;
    case 'medicines':    medicineCtrl($conn);         break;
    case 'customers':    customerCtrl($conn);         break;
    case 'orders':       ordersCtrl($conn);           break;
    case 'history':      historyCtrl($conn);          break;
    default:
        if (isset($_SESSION['user_id'])) {
            header('Location: index.php?page=home');
        } else {
            header('Location: index.php?page=login');
        }
        exit;
}

mysqli_close($conn);
?>