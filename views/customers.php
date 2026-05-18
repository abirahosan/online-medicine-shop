<?php $user = $_SESSION['user']; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Customers &mdash; Online Medicine Shop</title>
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
            <a href="index.php?page=home" class="nav-link">Home</a>
            <a href="index.php?page=dashboard"  class="nav-link">Dashboard</a>
            <a href="index.php?page=categories" class="nav-link">Categories</a>
            <a href="index.php?page=medicines"  class="nav-link">Medicines</a>
            <a href="index.php?page=customers"  class="nav-link active">Customers</a>
            <a href="index.php?page=orders"     class="nav-link">Orders</a>
            <a href="index.php?page=history"    class="nav-link">History</a>
        </nav>
        <div class="nav-user">
            <a href="index.php?page=profile" class="user-pill">
                <span class="user-avatar">
                    <?php if (!empty($user['profile_picture'])): ?>
                        <img src="/online-medicine-shop/public/uploads/profiles/<?= htmlspecialchars($user['profile_picture']) ?>"
                            alt="<?= htmlspecialchars($user['name']) ?>"
                            style="width:28px;height:28px;border-radius:50%;object-fit:cover;display:block;">
                    <?php else: ?>
                        <?= strtoupper(substr($user['name'], 0, 1)) ?>
                    <?php endif; ?>
                </span>
                <span class="user-meta">
                    <span class="user-name"><?= htmlspecialchars($user['name']) ?></span>
                    <span class="user-role">Admin</span>
                </span>
            </a>
            <a href="index.php?page=logout" class="btn-logout">Logout</a>
        </div>
    </div>
</header>

<main class="main-content">
    <div class="page-header">
        <div>
            <h1 class="page-title">Manage Customers</h1>
            <p class="page-sub">View and delete registered customers</p>
        </div>
    </div>


    <?php if (isset($_GET['msg'])): ?>
        <?php $messages = [
            'deleted' => 'Customer deleted successfully.'
        ];
        $msg = $messages[$_GET['msg']] ?? null; ?>
        <?php if ($msg): ?>
            <div class="alert alert-success"><?= $msg ?></div>
        <?php endif; ?>
    <?php endif; ?>


    <div class="card">
        <div class="card-toolbar">
            <span class="badge"><?= count($customers) ?> total</span>
        </div>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Address</th>
                        <th>Joined</th>
                        <th style="text-align:right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($customers)): ?>
                        <tr><td colspan="7" class="empty">No customers yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($customers as $i => $cust): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td><?= htmlspecialchars($cust['name']) ?></td>
                                <td><?= htmlspecialchars($cust['email']) ?></td>
                                <td><?= htmlspecialchars($cust['phone'] ?? '—') ?></td>
                                <td><?= htmlspecialchars($cust['address'] ?? '—') ?></td>
                                <td><?= date('d M Y', strtotime($cust['created_at'])) ?></td>
                                <td class="text-right">
                                    <a class="btn-sm btn-delete"
                                       href="index.php?page=customers&action=delete&id=<?= $cust['id'] ?>"
                                       onclick="return confirm('Delete this customer? This will also remove their cart and orders.')">
                                       Delete
                                    </a>
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

</body>
</html>