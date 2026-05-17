<?php $user = $_SESSION['user']; $isEdit = !empty($editing); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Medicines &mdash; Online Medicine Shop</title>
<link rel="stylesheet" href="admin.css">
</head>
<body class="app-body">

<header class="navbar">
    <div class="navbar-inner">
        <a class="brand" href="index.php?page=dashboard">
            <span class="brand-icon">&#128138;</span>
            <span>MediShop Admin</span>
        </a>
        <nav class="nav-links">
            <a href="index.php?page=dashboard"  class="nav-link">Dashboard</a>
            <a href="index.php?page=categories" class="nav-link">Categories</a>
            <a href="index.php?page=medicines"  class="nav-link active">Medicines</a>
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
            <h1 class="page-title">Manage Medicines</h1>
            <p class="page-sub">Add, edit and delete medicines</p>
        </div>
    </div>


    <?php if (isset($_GET['msg'])): ?>
        <?php $messages = [
            'added'   => 'Medicine added successfully.',
            'updated' => 'Medicine updated successfully.',
            'deleted' => 'Medicine deleted successfully.',
            'blocked' => 'Cannot delete — medicine is in a pending order.'
        ];
        $msg = $messages[$_GET['msg']] ?? null; ?>
        <?php if ($msg): ?>
            <div class="alert <?= $_GET['msg'] === 'blocked' ? 'alert-error' : 'alert-success' ?>">
                <?= $msg ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>


    <div class="card form-card">
        <h3 class="card-title">
            <?= $isEdit ? '&#9998; Edit Medicine (#' . intval($editing['id']) . ')' : '+ Add New Medicine' ?>
        </h3>
        <form method="POST"
              action="index.php?page=medicines&action=<?= $isEdit ? 'update&id=' . intval($editing['id']) : 'add' ?>"
              enctype="multipart/form-data"
              class="form" novalidate>

            <div class="field-row">
                <div class="field">
                    <label for="name">Medicine Name</label>
                    <input type="text" id="name" name="name"
                           value="<?= htmlspecialchars($editing['name'] ?? '') ?>"
                           placeholder="e.g. Napa Extra" required>
                    <span class="field-error" id="nameErr"></span>
                </div>
                <div class="field">
                    <label for="category_id">Category</label>
                    <select id="category_id" name="category_id" required>
                        <option value="">-- Select Category --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>"
                                <?= ($editing['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['name']) ?> (<?= ucfirst($cat['category_type']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <span class="field-error" id="catErr"></span>
                </div>
            </div>

            <div class="field-row">
                <div class="field">
                    <label for="vendor_name">Vendor Name</label>
                    <input type="text" id="vendor_name" name="vendor_name"
                           value="<?= htmlspecialchars($editing['vendor_name'] ?? '') ?>"
                           placeholder="e.g. Square Pharma" required>
                    <span class="field-error" id="vendorErr"></span>
                </div>
                <div class="field">
                    <label for="price">Price (BDT)</label>
                    <input type="number" id="price" name="price" step="0.01" min="0.01"
                           value="<?= htmlspecialchars($editing['price'] ?? '') ?>"
                           placeholder="e.g. 10.50" required>
                    <span class="field-error" id="priceErr"></span>
                </div>
            </div>

            <div class="field-row">
                <div class="field">
                    <label for="availability">Stock (units)</label>
                    <input type="number" id="availability" name="availability" min="0"
                           value="<?= htmlspecialchars($editing['availability'] ?? '') ?>"
                           placeholder="e.g. 100" required>
                    <span class="field-error" id="stockErr"></span>
                </div>
                <div class="field">
                    <label for="image">
                        Image (JPEG/PNG, max 2MB)
                        <?php if ($isEdit && !empty($editing['image_path'])): ?>
                            <span class="hint">— leave blank to keep current</span>
                        <?php endif; ?>
                    </label>
                    <input type="file" id="image" name="image" accept="image/jpeg,image/png">
                    <span class="field-error" id="imgErr"></span>
                    <?php if ($isEdit && !empty($editing['image_path'])): ?>
                        <img src="<?= htmlspecialchars($editing['image_path']) ?>"
                             alt="Current image" class="img-preview">
                    <?php endif; ?>
                </div>
            </div>

            <div class="field">
                <label for="description">Description</label>
                <textarea id="description" name="description"
                          placeholder="Optional description..."
                          rows="3"><?= htmlspecialchars($editing['description'] ?? '') ?></textarea>
            </div>

            <div class="form-actions">
                <?php if ($isEdit): ?>
                    <a href="index.php?page=medicines" class="btn btn-ghost">Cancel</a>
                    <button type="submit" class="btn btn-primary">Update Medicine</button>
                <?php else: ?>
                    <button type="submit" class="btn btn-primary">Save Medicine</button>
                <?php endif; ?>
            </div>
        </form>
    </div>


    <div class="card">
        <div class="card-toolbar">
            <span class="badge"><?= count($medicines) ?> total</span>
        </div>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Vendor</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th style="text-align:right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($medicines)): ?>
                        <tr><td colspan="8" class="empty">No medicines yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($medicines as $i => $med): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td>
                                    <?php if (!empty($med['image_path'])): ?>
                                        <img src="<?= htmlspecialchars($med['image_path']) ?>"
                                             alt="<?= htmlspecialchars($med['name']) ?>"
                                             class="table-img">
                                    <?php else: ?>
                                        <span class="no-img">No image</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($med['name']) ?></td>
                                <td>
                                    <?= htmlspecialchars($med['category_name']) ?>
                                    <span class="badge badge--<?= $med['category_type'] ?>">
                                        <?= ucfirst($med['category_type']) ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($med['vendor_name']) ?></td>
                                <td>&#2547;<?= number_format($med['price'], 2) ?></td>
                                <td>
                                        <?= $med['availability'] ?>
                                </td>
                                <td class="text-right">
                                    <a class="btn-sm btn-edit"
                                       href="index.php?page=medicines&action=edit&id=<?= $med['id'] ?>">Edit</a>
                                    <a class="btn-sm btn-delete"
                                       href="index.php?page=medicines&action=delete&id=<?= $med['id'] ?>"
                                       onclick="return confirm('Delete this medicine?')">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<footer class="footer">&copy; <?= date('Y') ?> Online Medicine Shop</footer>


<script>
(function () {
    var form     = document.querySelector('.form');
    var nameIn   = document.getElementById('name');
    var catIn    = document.getElementById('category_id');
    var vendorIn = document.getElementById('vendor_name');
    var priceIn  = document.getElementById('price');
    var stockIn  = document.getElementById('availability');
    var imgIn    = document.getElementById('image');

    var nameErr  = document.getElementById('nameErr');
    var catErr   = document.getElementById('catErr');
    var vendorErr= document.getElementById('vendorErr');
    var priceErr = document.getElementById('priceErr');
    var stockErr = document.getElementById('stockErr');
    var imgErr   = document.getElementById('imgErr');

    form.addEventListener('submit', function (e) {
        var valid = true;
        [nameErr, catErr, vendorErr, priceErr, stockErr, imgErr]
            .forEach(function(el){ el.textContent = ''; });

        if (nameIn.value.trim() === '') {
            nameErr.textContent = 'Medicine name is required.';
            valid = false;
        }
        if (catIn.value === '') {
            catErr.textContent = 'Please select a category.';
            valid = false;
        }
        if (vendorIn.value.trim() === '') {
            vendorErr.textContent = 'Vendor name is required.';
            valid = false;
        }
        if (priceIn.value === '' || parseFloat(priceIn.value) <= 0) {
            priceErr.textContent = 'Price must be greater than 0.';
            valid = false;
        }
        if (stockIn.value === '' || parseInt(stockIn.value) < 0) {
            stockErr.textContent = 'Stock must be 0 or more.';
            valid = false;
        }
        if (imgIn.files.length > 0) {
            var file     = imgIn.files[0];
            var allowed  = ['image/jpeg', 'image/png'];
            var maxSize  = 2 * 1024 * 1024;
            if (!allowed.includes(file.type)) {
                imgErr.textContent = 'Only JPEG or PNG allowed.';
                valid = false;
            } else if (file.size > maxSize) {
                imgErr.textContent = 'Image must be under 2MB.';
                valid = false;
            }
        }
        if (!valid) e.preventDefault();
    });
})();
</script>

</body>
</html>