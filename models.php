<?php
// ================================================================
// MODELS - All DB access using procedural mysqli + prepared stmts
// ================================================================

/* ===================== USERS ===================== */

function getUserByEmail($conn, $email) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE email = ?");
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    return $row ?: null;
}

function getUserById($conn, $id) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    return $row ?: null;
}

function emailExists($conn, $email, $excludeId = null) {
    if ($excludeId !== null) {
        $stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ? AND id != ?");
        mysqli_stmt_bind_param($stmt, 'si', $email, $excludeId);
    } else {
        $stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ?");
        mysqli_stmt_bind_param($stmt, 's', $email);
    }
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    $exists = mysqli_stmt_num_rows($stmt) > 0;
    mysqli_stmt_close($stmt);
    return $exists;
}

function createUser($conn, $name, $email, $password, $role, $address, $phone) {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = mysqli_prepare($conn,
        "INSERT INTO users (name, email, password_hash, role, address, phone)
         VALUES (?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, 'ssssss', $name, $email, $hash, $role, $address, $phone);
    $ok = mysqli_stmt_execute($stmt);
    $id = mysqli_insert_id($conn);
    mysqli_stmt_close($stmt);
    return $ok ? $id : 0;
}

function updateUserProfile($conn, $id, $name, $email, $address, $phone, $picturePath) {
    if ($picturePath !== null) {
        $stmt = mysqli_prepare($conn,
            "UPDATE users SET name=?, email=?, address=?, phone=?, profile_picture=? WHERE id=?");
        mysqli_stmt_bind_param($stmt, 'sssssi', $name, $email, $address, $phone, $picturePath, $id);
    } else {
        $stmt = mysqli_prepare($conn,
            "UPDATE users SET name=?, email=?, address=?, phone=? WHERE id=?");
        mysqli_stmt_bind_param($stmt, 'ssssi', $name, $email, $address, $phone, $id);
    }
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function updateUserPassword($conn, $id, $newPassword) {
    $hash = password_hash($newPassword, PASSWORD_DEFAULT);
    $stmt = mysqli_prepare($conn, "UPDATE users SET password_hash=? WHERE id=?");
    mysqli_stmt_bind_param($stmt, 'si', $hash, $id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

/* ===================== CATEGORIES ===================== */

function getAllCategories($conn) {
    $r = mysqli_query($conn, "SELECT * FROM categories ORDER BY name ASC");
    return mysqli_fetch_all($r, MYSQLI_ASSOC);
}

function getCategoryById($conn, $id) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM categories WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    return $row ?: null;
}

function getCategoriesWithCount($conn) {
    $r = mysqli_query($conn,
        "SELECT c.*, COUNT(m.id) AS medicine_count
         FROM categories c
         LEFT JOIN medicines m ON m.category_id = c.id
         GROUP BY c.id
         ORDER BY c.name ASC");
    return mysqli_fetch_all($r, MYSQLI_ASSOC);
}

/* ===================== MEDICINES ===================== */

function getMedicines($conn, $categoryId = null, $categoryType = null) {
    $sql    = "SELECT m.*, c.name AS category_name, c.category_type
               FROM medicines m
               JOIN categories c ON c.id = m.category_id
               WHERE 1=1";
    $params = [];
    $types  = '';

    if ($categoryId !== null) {
        $sql     .= " AND m.category_id = ?";
        $types   .= 'i';
        $params[] = $categoryId;
    }
    if ($categoryType !== null && $categoryType !== '') {
        $sql     .= " AND c.category_type = ?";
        $types   .= 's';
        $params[] = $categoryType;
    }

    $sql .= " ORDER BY m.created_at DESC";
    $stmt = mysqli_prepare($conn, $sql);

    if ($params) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }

    mysqli_stmt_execute($stmt);
    $rows = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
    return $rows;
}

function getMedicineById($conn, $id) {
    $stmt = mysqli_prepare($conn,
        "SELECT m.*, c.name AS category_name, c.category_type
         FROM medicines m
         JOIN categories c ON c.id = m.category_id
         WHERE m.id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    return $row ?: null;
}

function searchMedicines($conn, $q, $vendor, $genre) {
    $sql    = "SELECT m.*, c.name AS category_name, c.category_type
               FROM medicines m
               JOIN categories c ON c.id = m.category_id
               WHERE 1=1";
    $params = [];
    $types  = '';

    if ($q !== '') {
        $sql     .= " AND m.name LIKE ?";
        $types   .= 's';
        $like     = '%' . $q . '%';
        $params[] = $like;
    }
    if ($vendor !== '') {
        $sql     .= " AND m.vendor_name LIKE ?";
        $types   .= 's';
        $like     = '%' . $vendor . '%';
        $params[] = $like;
    }
    if ($genre !== '') {
        $sql     .= " AND c.name LIKE ?";
        $types   .= 's';
        $like     = '%' . $genre . '%';
        $params[] = $like;
    }

    $sql .= " ORDER BY m.created_at DESC";
    $stmt = mysqli_prepare($conn, $sql);

    if ($params) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }

    mysqli_stmt_execute($stmt);
    $rows = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
    return $rows;
}

function getAllVendors($conn) {
    $r    = mysqli_query($conn,
        "SELECT DISTINCT vendor_name FROM medicines ORDER BY vendor_name ASC");
    $rows = mysqli_fetch_all($r, MYSQLI_ASSOC);
    return array_column($rows, 'vendor_name');
}
