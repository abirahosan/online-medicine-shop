<?php


// Register 
function registerCtrl($conn) {
    $error = $success = '';
    $old = ['name' => '', 'email' => '', 'role' => 'customer', 'address' => '', 'phone' => ''];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name     = trim($_POST['name']    ?? '');
        $email    = trim($_POST['email']   ?? '');
        $password = $_POST['password']     ?? '';
        $confirm  = $_POST['confirm']      ?? '';
        $role     = $_POST['role']         ?? 'customer';
        $address  = trim($_POST['address'] ?? '');
        $phone    = trim($_POST['phone']   ?? '');
        $old = compact('name', 'email', 'role', 'address', 'phone');

        if ($name === '' || $email === '' || $password === '') {
            $error = 'Name, email and password are required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Enter a valid email address.';
        } elseif (strlen($password) < 8) {
            $error = 'Password must be at least 8 characters.';
        } elseif (!preg_match('/[A-Z]/', $password)) {
            $error = 'Password must contain at least one uppercase letter.';
        } elseif (!preg_match('/[a-z]/', $password)) {
            $error = 'Password must contain at least one lowercase letter.';
        } elseif (!preg_match('/[0-9]/', $password)) {
            $error = 'Password must contain at least one number.';
        } elseif (!preg_match('/[\W_]/', $password)) {
            $error = 'Password must contain at least one special character.';
        } elseif ($password !== $confirm) {
            $error = 'Passwords do not match.';
        } elseif ($role !== 'customer') {
            $error = 'Invalid role selected.';
        } elseif (emailExists($conn, $email)) {
            $error = 'That email is already registered.';
        } else {
            if (createUser($conn, $name, $email, $password, $role, $address, $phone)) {
                $success = 'Account created! You can now log in.';
                $old = ['name' => '', 'email' => '', 'role' => 'customer', 'address' => '', 'phone' => ''];
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
    }

    require 'views/register.php';
}

//Login 
function loginCtrl($conn) {
    $error   = '';
    $prefill = $_COOKIE['remember_email'] ?? '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email    = trim($_POST['email']    ?? '');
        $password = $_POST['password']      ?? '';
        $remember = isset($_POST['remember']);

        if ($email === '' || $password === '') {
            $error = 'Please fill in both fields.';
        } else {
            $user = getUserByEmail($conn, $email);
            if ($user && password_verify($password, $user['password_hash'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['name']    = $user['name'];
                $_SESSION['role']    = $user['role'];

                if ($remember) {
                    setcookie('remember_email', $email, time() + 86400 * 30, '/');
                } else {
                    setcookie('remember_email', '', time() - 3600, '/');
                }

                header('Location: index.php?page=home');
                exit;
            }
            $error = 'Invalid email or password.';
        }
    }

    require 'views/login.php';
}

// Profile 
function profileCtrl($conn) {
    $user    = getUserById($conn, $_SESSION['user_id']);
    $error   = $success = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';

        //Update info 
        if ($action === 'update_info') {
            $name    = trim($_POST['name']    ?? '');
            $email   = trim($_POST['email']   ?? '');
            $address = trim($_POST['address'] ?? '');
            $phone   = trim($_POST['phone']   ?? '');

            if ($name === '' || $email === '') {
                $error = 'Name and email are required.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Enter a valid email address.';
            } elseif (emailExists($conn, $email, $_SESSION['user_id'])) {
                $error = 'That email is used by another account.';
            } else {
                if (updateUserProfile($conn, $_SESSION['user_id'], $name, $email, $address, $phone)) {
                    $_SESSION['name'] = $name;
                    $user    = getUserById($conn, $_SESSION['user_id']);
                    $success = 'Profile updated successfully.';
                } else {
                    $error = 'Update failed. Try again.';
                }
            }
        }

        //Update picture 
        if ($action === 'update_picture') {
            if (empty($_FILES['profile_picture']['name'])) {
                $error = 'Please choose an image file.';
            } else {
                $allowed = ['image/jpeg', 'image/png', 'image/jpg'];
                $maxSize = 2 * 1024 * 1024;
                $mime    = mime_content_type($_FILES['profile_picture']['tmp_name']);
                $size    = $_FILES['profile_picture']['size'];

                if (!in_array($mime, $allowed)) {
                    $error = 'Only JPEG and PNG images are allowed.';
                } elseif ($size > $maxSize) {
                    $error = 'Image must be under 2MB.';
                } else {
                    $ext      = pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
                    $filename = uniqid('pfp_', true) . '.' . strtolower($ext);
                    $dest     = __DIR__ . '/public/uploads/profiles/' . $filename;

                    if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $dest)) {
                        updateUserPicture($conn, $_SESSION['user_id'], $filename);
                        $user    = getUserById($conn, $_SESSION['user_id']);
                        $success = 'Profile picture updated.';
                    } else {
                        $error = 'Upload failed. Check folder permissions.';
                    }
                }
            }
        }

        // Change password 
        if ($action === 'change_password') {
            $current = $_POST['current_password'] ?? '';
            $new     = $_POST['new_password']      ?? '';
            $confirm = $_POST['confirm_password']  ?? '';
            $hash    = getUserPasswordHash($conn, $_SESSION['user_id']);

            if (!password_verify($current, $hash)) {
                $error = 'Current password is incorrect.';
            } elseif (strlen($new) < 8) {
                $error = 'New password must be at least 8 characters.';
            } elseif (!preg_match('/[A-Z]/', $new)) {
                $error = 'Password must contain at least one uppercase letter.';
            } elseif (!preg_match('/[a-z]/', $new)) {
                $error = 'Password must contain at least one lowercase letter.';
            } elseif (!preg_match('/[0-9]/', $new)) {
                $error = 'Password must contain at least one number.';
            } elseif (!preg_match('/[\W_]/', $new)) {
                $error = 'Password must contain at least one special character.';
            } elseif ($new !== $confirm) {
                $error = 'New passwords do not match.';
            } else {
                updateUserPassword($conn, $_SESSION['user_id'], $new);
                $success = 'Password changed successfully.';
            }
        }
    }

    require 'views/profile.php';
}

//Home 
function homeCtrl($conn) {
    $categories = getCategories($conn);
    $medicines  = getMedicines($conn);
    require 'views/home.php';
}

//Categories 
function categoriesCtrl($conn) {
    $categories  = getCategories($conn);
    $activeCatId = intval($_GET['cat']  ?? 0);
    $typeFilter  = $_GET['type'] ?? '';

    if ($activeCatId > 0) {
        $medicines = getMedicinesByCategory($conn, $activeCatId);
    } else {
        $medicines = getMedicines($conn);
    }

    if (in_array($typeFilter, ['liquid', 'solid'])) {
        $medicines = array_values(array_filter($medicines,
            fn($m) => $m['category_type'] === $typeFilter));
    }

    require 'views/categories.php';
}

//AJAX Search 
function ajaxSearchCtrl($conn) {
    header('Content-Type: application/json');
    if (!isset($_SESSION['user_id'])) {
        http_response_code(403);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }

    $q      = trim($_GET['q']      ?? '');
    $vendor = trim($_GET['vendor'] ?? '');
    $catId  = intval($_GET['cat']  ?? 0);

    $results = searchMedicines($conn, $q, $vendor, $catId);

    $out = [];
    foreach ($results as $m) {
        $out[] = [
            'id'            => $m['id'],
            'name'          => htmlspecialchars($m['name']),
            'vendor_name'   => htmlspecialchars($m['vendor_name']),
            'price'         => number_format($m['price'], 2),
            'availability'  => $m['availability'],
            'category_name' => htmlspecialchars($m['category_name']),
            'category_type' => $m['category_type'],
            'image_path'    => $m['image_path'] ?? '',
        ];
    }

    echo json_encode($out);
    exit;
}
?>