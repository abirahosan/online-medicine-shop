<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Categories &mdash; MediShop</title>
<link rel="stylesheet" href="style.css">
</head>
<body class="app-body">

<!-- Navbar -->
<header class="navbar">
    <div class="navbar-inner">
        <a class="brand" href="index.php?page=home">
            <span class="brand-icon">&#128138;</span>
            <span>MediShop</span>
        </a>
        <nav class="nav-links">
            <a class="nav-link" href="index.php?page=home">Home</a>
            <a class="nav-link active" href="index.php?page=categories">Categories</a>
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <a class="nav-link" href="index.php?page=admin">Dashboard</a>
            <?php endif; ?>
        </nav>
        <div class="nav-user">
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="index.php?page=profile">
                    <span class="user-pill">
                        <span class="user-avatar"><?= strtoupper(substr($_SESSION['name'], 0, 1)) ?></span>
                        <span class="user-meta">
                            <span class="user-name"><?= htmlspecialchars($_SESSION['name']) ?></span>
                            <span class="user-role"><?= htmlspecialchars($_SESSION['role']) ?></span>
                        </span>
                    </span>
                </a>
                <a href="index.php?page=logout" class="btn-logout">Logout</a>
            <?php else: ?>
                <a href="index.php?page=login"    class="btn btn-ghost"   style="padding:7px 14px;font-size:12px;">Login</a>
                <a href="index.php?page=register" class="btn btn-primary" style="padding:7px 14px;font-size:12px;">Register</a>
            <?php endif; ?>
        </div>
    </div>
</header>

<main class="main-content">

    <div class="page-header">
        <div>
            <h1 class="page-title">Categories</h1>
            <p class="page-sub">Browse medicines by category or filter by type</p>
        </div>
        <span class="badge" id="resultCount"><?= count($medicines) ?> medicines</span>
    </div>

    <!-- Category pills -->
    <div class="category-list">
        <a class="cat-pill <?= ($activeCatId === 0 && $typeFilter === '') ? 'active' : '' ?>"
           href="index.php?page=categories">All</a>
        <a class="cat-pill <?= $typeFilter === 'liquid' ? 'active' : '' ?>"
           href="index.php?page=categories&type=liquid">Liquid</a>
        <a class="cat-pill <?= $typeFilter === 'solid' ? 'active' : '' ?>"
           href="index.php?page=categories&type=solid">Solid</a>
        <?php foreach ($categories as $cat): ?>
            <a class="cat-pill <?= $activeCatId === $cat['id'] ? 'active' : '' ?>"
               href="index.php?page=categories&cat=<?= $cat['id'] ?>">
                <?= htmlspecialchars($cat['name']) ?>
                <span style="font-size:10px;opacity:.6;">(<?= htmlspecialchars($cat['category_type']) ?>)</span>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Medicine list -->
    <div class="medicine-list">
        <?php if (empty($medicines)): ?>
            <p style="color:var(--text-muted);font-size:13px;font-style:italic;">No medicines found in this category.</p>
        <?php else: ?>
            <?php foreach ($medicines as $m): ?>
                <div class="medicine-card">
                    <?php if (!empty($m['image_path'])): ?>
                        <img src="public/uploads/medicines/<?= htmlspecialchars($m['image_path']) ?>"
                             alt="<?= htmlspecialchars($m['name']) ?>">
                    <?php else: ?>
                        <div style="width:100%;height:150px;background:var(--bg-3);display:flex;align-items:center;justify-content:center;font-size:32px;">&#128138;</div>
                    <?php endif; ?>
                    <div class="medicine-card-body">
                        <div class="medicine-card-name"><?= htmlspecialchars($m['name']) ?></div>
                        <div class="medicine-card-vendor">
                            <?= htmlspecialchars($m['vendor_name']) ?> &middot;
                            <?= htmlspecialchars($m['category_name']) ?>
                            <span style="font-size:10px;opacity:.6;">(<?= htmlspecialchars($m['category_type']) ?>)</span>
                        </div>
                        <div class="medicine-card-footer">
                            <span class="medicine-price">$<?= number_format($m['price'], 2) ?></span>
                            <?php if ($m['availability'] > 0): ?>
                                <span class="stock-badge stock-in">In Stock</span>
                            <?php else: ?>
                                <span class="stock-badge stock-out">Out of Stock</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

</main>

<footer class="footer">&copy; <?= date('Y') ?> MediShop. All rights reserved.</footer>

</body>
</html>