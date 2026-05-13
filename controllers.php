<?php
// ================================================================
// CONTROLLERS - Task 1: Auth, Profile, Home, Browse, AJAX Search
// ================================================================

/* ============== CSRF helpers ============== */
function csrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrf() {
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(403);
        die('CSRF validation failed.');
    }
}

/* ============== Login ============== */
function loginCtrl($conn) {
    $error   = '';
    $prefill = $_COOKIE['remember_email'] ?? '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        verifyCsrf();
        $email    = trim($_POST['email']    ?? '');
        $password = $_POST['password']      ?? '';
        $remember = isset($_POST['remember']);

        if ($email === '' || $password === '') {
            $error = 'Please fill in both fields.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Enter a valid email address.';
        } else {
            $user = getUserByEmail($conn, $email);
            if ($user && password_verify($password, $user['password_hash'])) {
                session_regenerate_id(true);
                $_SESSION['user'] = [
                    'id'      => $user['id'],
                    'name'    => $user['name'],
                    'email'   => $user['email'],
                    'role'    => $user['role'],
                    'picture' => $user['profile_picture'],
                ];
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

/* ============== Register ============== */
function registerCtrl($conn) {
    $error   = '';
    $success = '';
    $old     = ['name' => '', 'email' => '', 'address' => '', 'phone' => '', 'role' => 'customer'];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        verifyCsrf();
        $name     = trim($_POST['name']            ?? '');
        $email    = trim($_POST['email']           ?? '');
        $password = $_POST['password']             ?? '';
        $confirm  = $_POST['confirm_password']     ?? '';
        $role     = $_POST['role']                 ?? 'customer';
        $address  = trim($_POST['address']         ?? '');
        $phone    = trim($_POST['phone']           ?? '');
        $old      = compact('name', 'email', 'address', 'phone', 'role');

        if ($name === '' || $email === '' || $password === '') {
            $error = 'Name, email, and password are required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Enter a valid email address.';
        } elseif (strlen($password) < 8) {
            $error = 'Password must be at least 8 characters.';
        } elseif ($password !== $confirm) {
            $error = 'Passwords do not match.';
        } elseif (!in_array($role, ['admin', 'customer'], true)) {
            $error = 'Invalid role selected.';
        } elseif (emailExists($conn, $email)) {
            $error = 'This email is already registered.';
        } else {
            $id = createUser($conn, $name, $email, $password, $role,
                             $address ?: null, $phone ?: null);
            if ($id) {
                $success = 'Account created! You can now log in.';
                $old = ['name' => '', 'email' => '', 'address' => '', 'phone' => '', 'role' => 'customer'];
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
    }

    require 'views/register.php';
}

/* ============== Logout ============== */
function logoutCtrl() {
    $_SESSION = [];
    session_destroy();
    setcookie('remember_email', '', time() - 3600, '/');
    header('Location: index.php?page=login');
    exit;
}

/* ============== Profile ============== */
function profileCtrl($conn) {
    if (!isset($_SESSION['user'])) {
        header('Location: index.php?page=login');
        exit;
    }

    $user    = getUserById($conn, $_SESSION['user']['id']);
    $error   = '';
    $success = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        verifyCsrf();
        $action = $_POST['action'] ?? 'profile';

        /* --- Update profile info + optional picture --- */
        if ($action === 'profile') {
            $name    = trim($_POST['name']    ?? '');
            $email   = trim($_POST['email']   ?? '');
            $address = trim($_POST['address'] ?? '');
            $phone   = trim($_POST['phone']   ?? '');

            if ($name === '' || $email === '') {
                $error = 'Name and email are required.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Enter a valid email address.';
            } elseif (emailExists($conn, $email, $user['id'])) {
                $error = 'This email is already used by another account.';
            } else {
                $picturePath = null;

                if (!empty($_FILES['profile_picture']['name'])) {
                    $file    = $_FILES['profile_picture'];
                    $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                    $maxSize = 2 * 1024 * 1024;

                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mime  = finfo_file($finfo, $file['tmp_name']);
                    finfo_close($finfo);

                    if ($file['error'] !== UPLOAD_ERR_OK) {
                        $error = 'File upload error. Please try again.';
                    } elseif (!in_array($mime, $allowed, true)) {
                        $error = 'Profile picture must be JPEG, PNG, GIF, or WebP.';
                    } elseif ($file['size'] > $maxSize) {
                        $error = 'Profile picture must be under 2 MB.';
                    } else {
                        $ext       = pathinfo($file['name'], PATHINFO_EXTENSION);
                        $filename  = 'user_' . $user['id'] . '_' . time() . '.' . strtolower($ext);
                        $uploadDir = 'public/uploads/profiles/';
                        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

                        if (move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
                            if (!empty($user['profile_picture']) && file_exists($user['profile_picture'])) {
                                unlink($user['profile_picture']);
                            }
                            $picturePath = $uploadDir . $filename;
                        } else {
                            $error = 'Failed to save profile picture.';
                        }
                    }
                }

                if ($error === '') {
                    updateUserProfile($conn, $user['id'], $name, $email,
                                      $address ?: null, $phone ?: null, $picturePath);
                    // Refresh session
                    $_SESSION['user']['name']  = $name;
                    $_SESSION['user']['email'] = $email;
                    if ($picturePath) $_SESSION['user']['picture'] = $picturePath;
                    $user    = getUserById($conn, $user['id']);
                    $success = 'Profile updated successfully.';
                }
            }
        }

        /* --- Change password --- */
        if ($action === 'password') {
            $current = $_POST['current_password'] ?? '';
            $new     = $_POST['new_password']      ?? '';
            $confirm = $_POST['confirm_password']  ?? '';

            if ($current === '' || $new === '' || $confirm === '') {
                $error = 'All password fields are required.';
            } elseif (!password_verify($current, $user['password_hash'])) {
                $error = 'Current password is incorrect.';
            } elseif (strlen($new) < 8) {
                $error = 'New password must be at least 8 characters.';
            } elseif ($new !== $confirm) {
                $error = 'New passwords do not match.';
            } else {
                updateUserPassword($conn, $user['id'], $new);
                $user    = getUserById($conn, $user['id']);
                $success = 'Password changed successfully.';
            }
        }
    }

    require 'views/profile.php';
}

/* ============== Home Page ============== */
function homeCtrl($conn) {
    $categories = getCategoriesWithCount($conn);
    $vendors    = getAllVendors($conn);

    $activeCat  = isset($_GET['category']) ? (int) $_GET['category'] : null;
    $activeType = isset($_GET['type']) && in_array($_GET['type'], ['liquid', 'solid'], true)
                  ? $_GET['type'] : null;

    $medicines  = getMedicines($conn, $activeCat, $activeType);
    $user       = $_SESSION['user'] ?? null;

    require 'views/home.php';
}

/* ============== AJAX Search Endpoint ============== */
function ajaxSearchCtrl($conn) {
    header('Content-Type: application/json');

    if (!isset($_SESSION['user'])) {
        http_response_code(403);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }

    $q      = trim($_GET['q']      ?? '');
    $vendor = trim($_GET['vendor'] ?? '');
    $genre  = trim($_GET['genre']  ?? '');

    $results = searchMedicines($conn, $q, $vendor, $genre);

    $out = [];
    foreach ($results as $m) {
        $out[] = [
            'id'            => (int)   $m['id'],
            'name'          =>         $m['name'],
            'vendor_name'   =>         $m['vendor_name'],
            'price'         => (float) $m['price'],
            'availability'  => (int)   $m['availability'],
            'category_name' =>         $m['category_name'],
            'category_type' =>         $m['category_type'],
            'image_path'    =>         $m['image_path'],
            'description'   =>         $m['description'],
        ];
    }

    echo json_encode($out);
    exit;
}
