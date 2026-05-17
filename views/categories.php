<?php $user = $_SESSION['user']; $isEdit = !empty($editing); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Categories &mdash; Online Medicine Shop</title>
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
            <a href="index.php?page=categories" class="nav-link active">Categories</a>
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
            <h1 class="page-title">Manage Categories</h1>
            <p class="page-sub">Add, edit and delete medicine categories</p>
        </div>
    </div>


    <?php if (isset($_GET['msg'])): ?>
        <?php $messages = [
            'added'   => 'Category added successfully.',
            'updated' => 'Category updated successfully.',
            'deleted' => 'Category deleted successfully.',
            'blocked' => 'Cannot delete — medicines exist under this category.'
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
            <?= $isEdit ? '&#9998; Edit Category (#' . intval($editing['id']) . ')' : '+ Add New Category' ?>
        </h3>
        <form method="POST"
              action="index.php?page=categories&action=<?= $isEdit ? 'update&id=' . intval($editing['id']) : 'add' ?>"
              class="form" novalidate>

            <div class="field-row">
                <div class="field">
                    <label for="name">Category Name</label>
                    <input type="text" id="name" name="name"
                           value="<?= htmlspecialchars($editing['name'] ?? '') ?>"
                           placeholder="e.g. Paracetamol genre" required>
                    <span class="field-error" id="nameErr"></span>
                </div>
                <div class="field">
                    <label for="category_type">Type</label>
                    <select id="category_type" name="category_type" required>
                        <option value="">-- Select Type --</option>
                        <option value="liquid" <?= ($editing['category_type'] ?? '') === 'liquid' ? 'selected' : '' ?>>Liquid</option>
                        <option value="solid"  <?= ($editing['category_type'] ?? '') === 'solid'  ? 'selected' : '' ?>>Solid</option>
                    </select>
                    <span class="field-error" id="typeErr"></span>
                </div>
            </div>

            <div class="form-actions">
                <?php if ($isEdit): ?>
                    <a href="index.php?page=categories" class="btn btn-ghost">Cancel</a>
                    <button type="submit" class="btn btn-primary">Update Category</button>
                <?php else: ?>
                    <button type="submit" class="btn btn-primary">Save Category</button>
                <?php endif; ?>
            </div>
        </form>
    </div>


    <div class="card">
        <div class="card-toolbar">
            <span class="badge"><?= count($categories) ?> total</span>
        </div>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Type</th>
                        <th style="text-align:right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($categories)): ?>
                        <tr><td colspan="5" class="empty">No categories yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($categories as $i => $cat): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td><?= htmlspecialchars($cat['name']) ?></td>
                                <td>
                                    <span class="badge badge--<?= $cat['category_type'] ?>">
                                        <?= ucfirst($cat['category_type']) ?>
                                    </span>
                                </td>
                                <td class="text-right">
                                    <a class="btn-sm btn-edit"
                                       href="index.php?page=categories&action=edit&id=<?= $cat['id'] ?>">Edit</a>
                                    <a class="btn-sm btn-delete"
                                       href="index.php?page=categories&action=delete&id=<?= $cat['id'] ?>"
                                       onclick="return confirm('Delete this category?')">Delete</a>
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
    var form    = document.querySelector('.form');
    var nameIn  = document.getElementById('name');
    var typeIn  = document.getElementById('category_type');
    var nameErr = document.getElementById('nameErr');
    var typeErr = document.getElementById('typeErr');

    form.addEventListener('submit', function (e) {
        var valid = true;
        nameErr.textContent = '';
        typeErr.textContent = '';

        if (nameIn.value.trim() === '') {
            nameErr.textContent = 'Category name is required.';
            valid = false;
        }
        if (typeIn.value === '') {
            typeErr.textContent = 'Please select a type.';
            valid = false;
        }
        if (!valid) e.preventDefault();
    });
})();
</script>

</body>
</html>