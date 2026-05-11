<?php
function getAllCategories($conn) {
    $r = mysqli_query($conn, "SELECT * FROM categories ORDER BY id DESC");
    return mysqli_fetch_all($r, MYSQLI_ASSOC);
}

function getCategory($conn, $id) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM categories WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    return $row;
}

function addCategory($conn, $name, $type) {
    $stmt = mysqli_prepare($conn,
        "INSERT INTO categories (name, category_type) VALUES (?, ?)");
    mysqli_stmt_bind_param($stmt, 'ss', $name, $type);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function updateCategory($conn, $id, $name, $type) {
    $stmt = mysqli_prepare($conn,
        "UPDATE categories SET name = ?, category_type = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'ssi', $name, $type, $id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function deleteCategory($conn, $id) {
    $stmt = mysqli_prepare($conn,
        "SELECT id FROM medicines WHERE category_id = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    if (mysqli_stmt_num_rows($stmt) > 0) {
        mysqli_stmt_close($stmt);
        return false;
    }
    mysqli_stmt_close($stmt);

    $stmt = mysqli_prepare($conn, "DELETE FROM categories WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}


function getAllMedicines($conn) {
    $r = mysqli_query($conn,
        "SELECT m.*, c.name AS category_name, c.category_type
         FROM medicines m
         JOIN categories c ON m.category_id = c.id
         ORDER BY m.id DESC");
    return mysqli_fetch_all($r, MYSQLI_ASSOC);
}

function getMedicine($conn, $id) {
    $stmt = mysqli_prepare($conn,
        "SELECT m.*, c.name AS category_name, c.category_type
         FROM medicines m
         JOIN categories c ON m.category_id = c.id
         WHERE m.id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    return $row;
}

function addMedicine($conn, $name, $category_id, $vendor, $price, $stock, $desc, $image_path) {
    $stmt = mysqli_prepare($conn,
        "INSERT INTO medicines (name, category_id, vendor_name, price, availability, description, image_path)
         VALUES (?, ?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, 'siidsss',
        $name, $category_id, $vendor, $price, $stock, $desc, $image_path);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function updateMedicine($conn, $id, $name, $category_id, $vendor, $price, $stock, $desc, $image_path) {
    $stmt = mysqli_prepare($conn,
        "UPDATE medicines SET name=?, category_id=?, vendor_name=?, price=?,
         availability=?, description=?, image_path=? WHERE id=?");
    mysqli_stmt_bind_param($stmt, 'siidsssi',
        $name, $category_id, $vendor, $price, $stock, $desc, $image_path, $id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function deleteMedicine($conn, $id) {
    $stmt = mysqli_prepare($conn,
        "SELECT oi.id FROM order_items oi
         JOIN orders o ON oi.order_id = o.id
         WHERE oi.medicine_id = ? AND o.status = 'pending' LIMIT 1");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    if (mysqli_stmt_num_rows($stmt) > 0) {
        mysqli_stmt_close($stmt);
        return false; 
    }
    mysqli_stmt_close($stmt);

    $stmt = mysqli_prepare($conn, "SELECT image_path FROM medicines WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    if ($row && $row['image_path'] && file_exists($row['image_path'])) {
        unlink($row['image_path']);
    }

    $stmt = mysqli_prepare($conn, "DELETE FROM medicines WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}


function getAllCustomers($conn) {
    $r = mysqli_query($conn,
        "SELECT id, name, email, phone, address, created_at
         FROM users WHERE role = 'customer' ORDER BY id DESC");
    return mysqli_fetch_all($r, MYSQLI_ASSOC);
}

function deleteCustomer($conn, $id) {
    $stmt = mysqli_prepare($conn,
        "DELETE FROM users WHERE id = ? AND role = 'customer'");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}



function getAllOrders($conn) {
    $r = mysqli_query($conn,
        "SELECT o.*, u.name AS customer_name, u.email AS customer_email
         FROM orders o
         JOIN users u ON o.user_id = u.id
         ORDER BY o.order_date DESC");
    return mysqli_fetch_all($r, MYSQLI_ASSOC);
}

function getOrderItems($conn, $order_id) {
    $stmt = mysqli_prepare($conn,
        "SELECT oi.*, m.name AS medicine_name
         FROM order_items oi
         JOIN medicines m ON oi.medicine_id = m.id
         WHERE oi.order_id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $order_id);
    mysqli_stmt_execute($stmt);
    $rows = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
    return $rows;
}

function updateOrderStatus($conn, $order_id, $status) {
    $stmt = mysqli_prepare($conn,
        "UPDATE orders SET status = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'si', $status, $order_id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function getAcceptedOrders($conn) {
    $r = mysqli_query($conn,
        "SELECT o.*, u.name AS customer_name, u.email AS customer_email
         FROM orders o
         JOIN users u ON o.user_id = u.id
         WHERE o.status = 'accepted'
         ORDER BY o.order_date DESC");
    return mysqli_fetch_all($r, MYSQLI_ASSOC);
}


function getDashboardCounts($conn) {
    $counts = [];

    $r = mysqli_query($conn, "SELECT COUNT(*) AS total FROM medicines");
    $counts['medicines'] = mysqli_fetch_assoc($r)['total'];

    $r = mysqli_query($conn, "SELECT COUNT(*) AS total FROM categories");
    $counts['categories'] = mysqli_fetch_assoc($r)['total'];

    $r = mysqli_query($conn, "SELECT COUNT(*) AS total FROM users WHERE role='customer'");
    $counts['customers'] = mysqli_fetch_assoc($r)['total'];

    $r = mysqli_query($conn, "SELECT COUNT(*) AS total FROM orders WHERE status='pending'");
    $counts['pending_orders'] = mysqli_fetch_assoc($r)['total'];

    return $counts;
}
?>