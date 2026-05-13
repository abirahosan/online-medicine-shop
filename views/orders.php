<?php $user = $_SESSION['user']; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Orders &mdash; Online Medicine Shop</title>
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
            <a href="index.php?page=dashboard"  class="nav-link">Dashboard</a>
            <a href="index.php?page=categories" class="nav-link">Categories</a>
            <a href="index.php?page=medicines"  class="nav-link">Medicines</a>
            <a href="index.php?page=customers"  class="nav-link">Customers</a>
            <a href="index.php?page=orders"     class="nav-link active">Orders</a>
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
            <h1 class="page-title">Purchase Requests</h1>
            <p class="page-sub">View and manage all customer orders</p>
        </div>
    </div>


    <div id="ajaxMsg" class="alert" style="display:none;"></div>


    <div class="card">
        <div class="card-toolbar">
            <span class="badge"><?= count($orders) ?> total</span>
        </div>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Email</th>
                        <th>Total</th>
                        <th>Shipping Address</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th style="text-align:right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($orders)): ?>
                        <tr><td colspan="9" class="empty">No orders yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($orders as $i => $order): ?>
                            <tr id="row-<?= $order['id'] ?>">
                                <td><?= $i + 1 ?></td>
                                <td>#<?= $order['id'] ?></td>
                                <td><?= htmlspecialchars($order['customer_name']) ?></td>
                                <td><?= htmlspecialchars($order['customer_email']) ?></td>
                                <td>&#2547;<?= number_format($order['total_amount'], 2) ?></td>
                                <td><?= htmlspecialchars($order['shipping_address']) ?></td>
                                <td><?= date('d M Y', strtotime($order['order_date'])) ?></td>
                                <td>
                                    <span class="badge badge--<?= $order['status'] ?>" id="status-<?= $order['id'] ?>">
                                        <?= ucfirst($order['status']) ?>
                                    </span>
                                </td>
                                <td class="text-right">
                                    <?php if ($order['status'] === 'pending'): ?>
                                        <button class="btn-sm btn-accept"
                                                onclick="updateOrder(<?= $order['id'] ?>, 'accepted')">
                                            Accept
                                        </button>
                                        <button class="btn-sm btn-delete"
                                                onclick="updateOrder(<?= $order['id'] ?>, 'rejected')">
                                            Reject
                                        </button>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
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
function updateOrder(orderId, status) {
    var label   = status === 'accepted' ? 'accept' : 'reject';
    if (!confirm('Are you sure you want to ' + label + ' this order?')) return;

    var formData = new FormData();
    formData.append('ajax', '1');
    formData.append('order_id', orderId);
    formData.append('status', status);

    fetch('index.php?page=orders', {
        method: 'POST',
        credentials: 'same-origin',
        body: formData
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        var msgBox = document.getElementById('ajaxMsg');
        if (data.success) {
            
            var badge = document.getElementById('status-' + orderId);
            badge.textContent = status.charAt(0).toUpperCase() + status.slice(1);
            badge.className   = 'badge badge--' + status;

            
            var row = document.getElementById('row-' + orderId);
            row.querySelector('.text-right').innerHTML = '<span class="text-muted">—</span>';

            
            msgBox.className     = 'alert alert-success';
            msgBox.textContent   = data.message;
            msgBox.style.display = 'block';
        } else {
            msgBox.className     = 'alert alert-error';
            msgBox.textContent   = data.message;
            msgBox.style.display = 'block';
        }
        
        setTimeout(function() { msgBox.style.display = 'none'; }, 3000);
    })
    .catch(function(err) {
        console.error(err);
    });
}
</script>

</body>
</html>