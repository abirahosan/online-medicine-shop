<?php


// USER MODELS (Task 1) 

function getUserByEmail($conn, $email) {
    $stmt = mysqli_prepare($conn,
        "SELECT id, name, email, password_hash, role, profile_picture, address, phone FROM users WHERE email = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    return $row;
}

function getUserById($conn, $id) {
    $stmt = mysqli_prepare($conn,
        "SELECT id, name, email, role, profile_picture, address, phone FROM users WHERE id = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    return $row;
}

function emailExists($conn, $email, $excludeId = 0) {
    $stmt = mysqli_prepare($conn,
        "SELECT id FROM users WHERE email = ? AND id != ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, 'si', $email, $excludeId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    $exists = mysqli_stmt_num_rows($stmt) > 0;
    mysqli_stmt_close($stmt);
    return $exists;
}

function createUser($conn, $name, $email, $password, $role, $address, $phone) {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = mysqli_prepare($conn,
        "INSERT INTO users (name, email, password_hash, role, address, phone) VALUES (?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, 'ssssss', $name, $email, $hash, $role, $address, $phone);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function updateUserProfile($conn, $id, $name, $email, $address, $phone) {
    $stmt = mysqli_prepare($conn,
        "UPDATE users SET name = ?, email = ?, address = ?, phone = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'ssssi', $name, $email, $address, $phone, $id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function updateUserPicture($conn, $id, $filename) {
    $stmt = mysqli_prepare($conn,
        "UPDATE users SET profile_picture = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'si', $filename, $id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function updateUserPassword($conn, $id, $newPassword) {
    $hash = password_hash($newPassword, PASSWORD_DEFAULT);
    $stmt = mysqli_prepare($conn,
        "UPDATE users SET password_hash = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'si', $hash, $id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function getUserPasswordHash($conn, $id) {
    $stmt = mysqli_prepare($conn,
        "SELECT password_hash FROM users WHERE id = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    return $row ? $row['password_hash'] : null;
}

// CATEGORY MODELS (Task 1 + Task 2) 

// Task 1 - simple list for home/browse pages
function getCategories($conn) {
    $r = mysqli_query($conn,
        "SELECT id, name, category_type FROM categories ORDER BY name ASC");
    return mysqli_fetch_all($r, MYSQLI_ASSOC);
}

// Task 2 - full list for admin management
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

//MEDICINE MODELS (Task 1 + Task 2) 

// Task 1 - simple list for home/browse pages
function getMedicines($conn) {
    $r = mysqli_query($conn,
        "SELECT m.id, m.name, m.vendor_name, m.price, m.availability, m.image_path,
                c.name AS category_name, c.category_type
         FROM medicines m
         JOIN categories c ON c.id = m.category_id
         ORDER BY m.id DESC");
    return mysqli_fetch_all($r, MYSQLI_ASSOC);
}

// Task 1 - medicines by category for browse page
function getMedicinesByCategory($conn, $categoryId) {
    $stmt = mysqli_prepare($conn,
        "SELECT m.id, m.name, m.vendor_name, m.price, m.availability, m.image_path,
                c.name AS category_name, c.category_type
         FROM medicines m
         JOIN categories c ON c.id = m.category_id
         WHERE m.category_id = ?
         ORDER BY m.id DESC");
    mysqli_stmt_bind_param($stmt, 'i', $categoryId);
    mysqli_stmt_execute($stmt);
    $rows = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
    return $rows;
}

// Task 1 - AJAX search
function searchMedicines($conn, $q, $vendor, $categoryId) {
    $sql = "SELECT m.id, m.name, m.vendor_name, m.price, m.availability, m.image_path,
                   c.name AS category_name, c.category_type
            FROM medicines m
            JOIN categories c ON c.id = m.category_id
            WHERE 1=1";
    $params = [];
    $types  = '';

    if ($q !== '') {
        $sql .= " AND m.name LIKE ?";
        $params[] = '%' . $q . '%';
        $types   .= 's';
    }
    if ($vendor !== '') {
        $sql .= " AND m.vendor_name LIKE ?";
        $params[] = '%' . $vendor . '%';
        $types   .= 's';
    }
    if ($categoryId > 0) {
        $sql .= " AND m.category_id = ?";
        $params[] = $categoryId;
        $types   .= 'i';
    }

    $sql .= " ORDER BY m.id DESC";
    $stmt = mysqli_prepare($conn, $sql);
    if (!empty($params)) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    mysqli_stmt_execute($stmt);
    $rows = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
    return $rows;
}

// Task 2 - full medicine with description for admin
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
    mysqli_stmt_bind_param($stmt, 'sisdiss',
        $name, $category_id, $vendor, $price, $stock, $desc, $image_path);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function updateMedicine($conn, $id, $name, $category_id, $vendor, $price, $stock, $desc, $image_path) {
    $stmt = mysqli_prepare($conn,
        "UPDATE medicines SET name=?, category_id=?, vendor_name=?, price=?,
         availability=?, description=?, image_path=? WHERE id=?");
    mysqli_stmt_bind_param($stmt, 'sisdsssi',
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
    if ($row && $row['image_path'] && file_exists(__DIR__ . '/' . $row['image_path'])) {
        unlink(__DIR__ . '/' . $row['image_path']);
    }

    $stmt = mysqli_prepare($conn, "DELETE FROM medicines WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

// CUSTOMER MODELS (Task 2) 

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

// ORDER MODELS (Task 2) 

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

// DASHBOARD MODELS (Task 2) 

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