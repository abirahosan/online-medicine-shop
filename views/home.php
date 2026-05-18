<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Home &mdash; MediShop</title>
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
            <a class="nav-link active" href="index.php?page=home">Home</a>
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <a class="nav-link" href="index.php?page=dashboard">Dashboard</a>
            <?php endif; ?>
        </nav>
        <div class="nav-user">
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="index.php?page=profile">
                    <span class="user-pill">
                        <span class="user-avatar">
                            <?php if (!empty($user['profile_picture'])): ?>
                                <img src="public/uploads/profiles/<?= htmlspecialchars($user['profile_picture']) ?>"
                                    alt="<?= htmlspecialchars($user['name']) ?>">
                            <?php else: ?>
                                <?= strtoupper(substr($_SESSION['name'], 0, 1)) ?>
                            <?php endif; ?>
                        </span>
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

    <!-- Search & Filter -->
    <div class="card form-card" style="margin-bottom:24px;">
        <h3 class="card-title">Search Medicines</h3>
        <div class="field-row">
            <div class="field">
                <label for="searchQ">Medicine Name</label>
                <input type="text" id="searchQ" class="search-input" placeholder="Search by name..." style="padding:11px 14px;width:100%;">
            </div>
            <div class="field">
                <label for="searchVendor">Vendor</label>
                <input type="text" id="searchVendor" placeholder="Filter by vendor..." style="padding:11px 14px;background:var(--bg-3);border:1px solid var(--border);border-radius:var(--radius-sm);font-size:14px;font-family:inherit;color:var(--text);width:100%;">
            </div>
            <div class="field">
                <label for="searchCat">Category</label>
                <select id="searchCat" style="padding:11px 14px;background:var(--bg-3);border:1px solid var(--border);border-radius:var(--radius-sm);font-size:14px;font-family:inherit;color:var(--text);width:100%;">
                    <option value="0">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?> (<?= htmlspecialchars($cat['category_type']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>

    <!-- Categories -->
    <div class="page-header">
        <div>
            <h1 class="page-title">Browse Medicines</h1>
            <p class="page-sub">Browse by category or search above</p>
        </div>
        <span class="badge" id="resultCount"><?= count($medicines) ?> medicines</span>
    </div>

    <div class="category-list">
        <a class="cat-pill active" href="index.php?page=home">All</a>
        <a class="cat-pill" href="index.php?page=home&type=liquid">Liquid</a>
        <a class="cat-pill" href="index.php?page=home&type=solid">Solid</a>
        <?php foreach ($categories as $cat): ?>
            <a class="cat-pill" href="index.php?page=home&cat=<?= $cat['id'] ?>">
                <?= htmlspecialchars($cat['name']) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Medicine list -->
    <div class="medicine-list" id="medicineList">
        <?php if (empty($medicines)): ?>
            <p style="color:var(--text-muted);font-size:13px;font-style:italic;">No medicines found.</p>
        <?php else: ?>
            <?php foreach ($medicines as $m): ?>
                <div class="medicine-card">
                    <?php if (!empty($m['image_path'])): ?>
                        <img src="<?= htmlspecialchars($m['image_path']) ?>"
                             alt="<?= htmlspecialchars($m['name']) ?>">
                    <?php else: ?>
                        <div style="width:100%;height:150px;background:var(--bg-3);display:flex;align-items:center;justify-content:center;font-size:32px;">&#128138;</div>
                    <?php endif; ?>
                    <div class="medicine-card-body">
                        <div class="medicine-card-name"><?= htmlspecialchars($m['name']) ?></div>
                        <div class="medicine-card-vendor"><?= htmlspecialchars($m['vendor_name']) ?> &middot; <?= htmlspecialchars($m['category_name']) ?></div>
                        <div class="medicine-card-footer">
                            <span class="medicine-price">৳<?= number_format($m['price'], 2) ?></span>
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

<!-- AJAX Search -->
<script>
(function () {
    var qInput      = document.getElementById('searchQ');
    var vendorInput = document.getElementById('searchVendor');
    var catSelect   = document.getElementById('searchCat');
    var list        = document.getElementById('medicineList');
    var counter     = document.getElementById('resultCount');
    var timer;

    function esc(s) {
        return String(s == null ? '' : s)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;').replace(/'/g, '&#039;');
    }

    function render(rows) {
        counter.textContent = rows.length + ' medicines';
        if (!rows.length) {
            list.innerHTML = '<p style="color:var(--text-muted);font-size:13px;font-style:italic;">No medicines found.</p>';
            return;
        }
        var html = '';
        rows.forEach(function (m) {
            // image_path already contains full relative path — use it directly
            var img = m.image_path
                ? '<img src="' + esc(m.image_path) + '" alt="' + esc(m.name) + '">'
                : '<div style="width:100%;height:150px;background:var(--bg-3);display:flex;align-items:center;justify-content:center;font-size:32px;">&#128138;</div>';
            var stock = parseInt(m.availability) > 0
                ? '<span class="stock-badge stock-in">In Stock</span>'
                : '<span class="stock-badge stock-out">Out of Stock</span>';
            html +=
                '<div class="medicine-card">' +
                    img +
                    '<div class="medicine-card-body">' +
                        '<div class="medicine-card-name">' + esc(m.name) + '</div>' +
                        '<div class="medicine-card-vendor">' + esc(m.vendor_name) + ' &middot; ' + esc(m.category_name) + '</div>' +
                        '<div class="medicine-card-footer">' +
                            '<span class="medicine-price">৳' + esc(m.price) + '</span>' +
                            stock +
                        '</div>' +
                    '</div>' +
                '</div>';
        });
        list.innerHTML = html;
    }

    function doSearch() {
        clearTimeout(timer);
        timer = setTimeout(function () {
            var q      = encodeURIComponent(qInput.value.trim());
            var vendor = encodeURIComponent(vendorInput.value.trim());
            var cat    = encodeURIComponent(catSelect.value);
            fetch('index.php?page=ajax_search&q=' + q + '&vendor=' + vendor + '&cat=' + cat,
                  { credentials: 'same-origin' })
                .then(function (r) { return r.json(); })
                .then(render)
                .catch(function (e) { console.error(e); });
        }, 250);
    }

    qInput.addEventListener('input', doSearch);
    vendorInput.addEventListener('input', doSearch);
    catSelect.addEventListener('change', doSearch);
})();
</script>

</body>
</html>