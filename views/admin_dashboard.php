<?php $user = $_SESSION['user']; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard &mdash; Online Medicine Shop</title>
<link rel="stylesheet" href="style.css">
</head>
<body class="app-body">

<header class="navbar">
    <div class="navbar-inner">
        <a class="brand" href="index.php?page=dashboard">
            <span class="brand-icon">&#128138;</span>
            <span>MediShop Admin</span>
        </a>
        <nav class="nav-links">
            <a href="index.php?page=dashboard"  class="nav-link active">Dashboard</a>
            <a href="index.php?page=categories" class="nav-link">Categories</a>
            <a href="index.php?page=medicines"  class="nav-link">Medicines</a>
            <a href="index.php?page=customers"  class="nav-link">Customers</a>
            <a href="index.php?page=orders"     class="nav-link">Orders</a>
            <a href="index.php?page=history"    class="nav-link">History</a>
        </nav>
        <div class="nav-user">
            <span class="user-pill">
                <span class="user-avatar"><?= strtoupper(substr($user['name'], 0, 1)) ?></span>
                <span class="user-meta">
                    <span class="user-name"><?= htmlspecialchars($user['name']) ?></span>
                    <span class="user-role">Admin</span>
                </span>
            </span>
            <a href="index.php?page=logout" class="btn-logout">Logout</a>
        </div>
    </div>
</header>

<main class="main-content">
    <div class="page-header">
        <div>
            <h1 class="page-title">Dashboard</h1>
            <p class="page-sub">Welcome back, <?= htmlspecialchars($user['name']) ?>!</p>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">&#128138;</div>
            <div class="stat-info">
                <span class="stat-number"><?= $counts['medicines'] ?></span>
                <span class="stat-label">Total Medicines</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">&#128193;</div>
            <div class="stat-info">
                <span class="stat-number"><?= $counts['categories'] ?></span>
                <span class="stat-label">Categories</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">&#128101;</div>
            <div class="stat-info">
                <span class="stat-number"><?= $counts['customers'] ?></span>
                <span class="stat-label">Customers</span>
            </div>
        </div>
        <div class="stat-card stat-card--alert">
            <div class="stat-icon">&#9203;</div>
            <div class="stat-info">
                <span class="stat-number"><?= $counts['pending_orders'] ?></span>
                <span class="stat-label">Pending Orders</span>
            </div>
        </div>
    </div>
    
</main>

<footer class="footer">&copy; <?= date('Y') ?> Online Medicine Shop</footer>

</body>
</html>