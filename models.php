<?php


//Users 
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

//Categories 
function getCategories($conn) {
    $r = mysqli_query($conn,
        "SELECT id, name, category_type FROM categories ORDER BY name ASC");
    return mysqli_fetch_all($r, MYSQLI_ASSOC);
}

function getCategoryById($conn, $id) {
    $stmt = mysqli_prepare($conn,
        "SELECT id, name, category_type FROM categories WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    return $row;
}

//Medicines
function getMedicines($conn) {
    $r = mysqli_query($conn,
        "SELECT m.id, m.name, m.vendor_name, m.price, m.availability, m.image_path,
                c.name AS category_name, c.category_type
         FROM medicines m
         JOIN categories c ON c.id = m.category_id
         ORDER BY m.id DESC");
    return mysqli_fetch_all($r, MYSQLI_ASSOC);
}

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
?>