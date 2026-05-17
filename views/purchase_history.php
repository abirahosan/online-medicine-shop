<?php $user = $_SESSION['user']; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Purchase History &mdash; Online Medicine Shop</title>
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
            <a href="index.php?page=medicines"  class="nav-link">Medicines</a>
            <a href="index.php?page=customers"  class="nav-link">Customers</a>
            <a href="index.php?page=orders"     class="nav-link">Orders</a>
            <a href="index.php?page=history"    class="nav-link active">History</a>
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
            <h1 class="page-title">Purchase History</h1>
            <p class="page-sub">All accepted orders and their details</p>
        </div>
    </div>

    <?php if (empty($orders)): ?>
        <div class="card">
            <p class="empty">No accepted orders yet.</p>
        </div>
    <?php else: ?>
        <?php foreach ($orders as $i => $order): ?>
            <div class="card order-card" style="padding: 32px;">

                <div class="order-header">
                    <div class="order-meta">
                        <span class="order-id">Order #<?= $order['id'] ?></span>
                        <span class="badge badge--accepted">Accepted</span>
                    </div>
                    <div class="order-info">
                        <span><strong>Customer:</strong> <?= htmlspecialchars($order['customer_name']) ?></span>
                        <span><strong>Email:</strong> <?= htmlspecialchars($order['customer_email']) ?></span>
                        <span><strong>Date:</strong> <?= date('d M Y, h:i A', strtotime($order['order_date'])) ?></span>
                        <span><strong>Payment:</strong> <?= htmlspecialchars($order['payment_method'] ?? '—') ?></span>
                        <span><strong>Shipping:</strong> <?= htmlspecialchars($order['shipping_address']) ?></span>
                    </div>
                </div>

                <div class="table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Medicine</th>
                                <th>Unit Price</th>
                                <th>Quantity</th>
                                <th style="text-align:right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $items = $orderItems[$order['id']] ?? [];
                            if (empty($items)): ?>
                                <tr><td colspan="5" class="empty">No items found.</td></tr>
                            <?php else: ?>
                                <?php foreach ($items as $j => $item): ?>
                                    <tr>
                                        <td><?= $j + 1 ?></td>
                                        <td><?= htmlspecialchars($item['medicine_name']) ?></td>
                                        <td>&#2547;<?= number_format($item['unit_price'], 2) ?></td>
                                        <td><?= $item['quantity'] ?></td>
                                        <td class="text-right">
                                            &#2547;<?= number_format($item['unit_price'] * $item['quantity'], 2) ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4" class="text-right"><strong>Total</strong></td>
                                <td class="text-right">
                                    <strong>&#2547;<?= number_format($order['total_amount'], 2) ?></strong>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</main>

<footer class="footer">&copy; <?= date('Y') ?> Online Medicine Shop</footer>

</body>
</html>