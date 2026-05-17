<?php


// REGISTER (Task 1) 
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
        } elseif (!in_array($role, ['admin', 'customer'])) {
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

// LOGIN (Task 1) 
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
                $_SESSION['user']    = $user;

                if ($remember) {
                    setcookie('remember_email', $email, time() + 86400 * 30, '/');
                } else {
                    setcookie('remember_email', '', time() - 3600, '/');
                }

                if ($user['role'] === 'admin') {
                    header('Location: index.php?page=dashboard');
                } else {
                    header('Location: index.php?page=home');
                }
                exit;
            }
            $error = 'Invalid email or password.';
        }
    }

    require 'views/login.php';
}

// PROFILE (Task 1) 
function profileCtrl($conn) {
    $user    = getUserById($conn, $_SESSION['user_id']);
    $error   = $success = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';

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

                    if (@move_uploaded_file($_FILES['profile_picture']['tmp_name'], $dest)) {
                        updateUserPicture($conn, $_SESSION['user_id'], $filename);
                        $user    = getUserById($conn, $_SESSION['user_id']);
                        $success = 'Profile picture updated.';
                    } else {
                        $error = 'Upload failed. Check folder permissions.';
                    }
                }
            }
        }

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

// HOME (Task 1) 
function homeCtrl($conn) {
    $categories = getCategories($conn);
    $medicines  = getMedicines($conn);
    require 'views/home.php';
}

// CATEGORIES BROWSE (Task 1) 
function categoriesBrowseCtrl($conn) {
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

    require 'views/home.php';
}

// AJAX SEARCH (Task 1) 
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

// ADMIN GATE (Task 2) 
function adminGate() {
    if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
        header('Location: index.php?page=login');
        exit;
    }
}

// DASHBOARD (Task 2) 
function dashboardCtrl($conn) {
    adminGate();
    $counts = getDashboardCounts($conn);
    require 'views/admin_dashboard.php';
}

// CATEGORY CONTROLLER (Task 2)
function categoryCtrl($conn) {
    adminGate();
    $action  = $_GET['action'] ?? 'list';
    $error   = '';
    $editing = null;

    if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = trim($_POST['name'] ?? '');
        $type = trim($_POST['category_type'] ?? '');

        if ($name === '' || $type === '') {
            $error = 'All fields are required.';
        } elseif (!in_array($type, ['liquid', 'solid'])) {
            $error = 'Category type must be liquid or solid.';
        } else {
            if (addCategory($conn, $name, $type)) {
                header('Location: index.php?page=categories&msg=added');
                exit;
            }
            $error = 'Failed to add category.';
        }
    }

    if ($action === 'edit' && $_SERVER['REQUEST_METHOD'] === 'GET') {
        $id      = intval($_GET['id'] ?? 0);
        $editing = getCategory($conn, $id);
    }

    if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id   = intval($_GET['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $type = trim($_POST['category_type'] ?? '');

        if ($name === '' || $type === '') {
            $error   = 'All fields are required.';
            $editing = ['id' => $id, 'name' => $name, 'category_type' => $type];
        } elseif (!in_array($type, ['liquid', 'solid'])) {
            $error   = 'Category type must be liquid or solid.';
            $editing = ['id' => $id, 'name' => $name, 'category_type' => $type];
        } else {
            if (updateCategory($conn, $id, $name, $type)) {
                header('Location: index.php?page=categories&msg=updated');
                exit;
            }
            $error = 'Failed to update category.';
        }
    }

    if ($action === 'delete') {
        $id     = intval($_GET['id'] ?? 0);
        $result = deleteCategory($conn, $id);
        if ($result === false) {
            header('Location: index.php?page=categories&msg=blocked');
        } else {
            header('Location: index.php?page=categories&msg=deleted');
        }
        exit;
    }

    $categories = getAllCategories($conn);
    require 'views/categories.php';
}

// MEDICINE CONTROLLER (Task 2) 
function medicineCtrl($conn) {
    adminGate();
    $action  = $_GET['action'] ?? 'list';
    $error   = '';
    $editing = null;

    $upload_dir = __DIR__ . '/public/uploads/medicines/';
    $upload_web = 'public/uploads/medicines/';

    if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $name        = trim($_POST['name'] ?? '');
        $category_id = intval($_POST['category_id'] ?? 0);
        $vendor      = trim($_POST['vendor_name'] ?? '');
        $price       = trim($_POST['price'] ?? '');
        $stock       = trim($_POST['availability'] ?? '');
        $desc        = trim($_POST['description'] ?? '');

        if ($name === '' || $category_id === 0 || $vendor === '' || $price === '' || $stock === '') {
            $error = 'All fields except description are required.';
        } elseif (!is_numeric($price) || floatval($price) <= 0) {
            $error = 'Price must be a positive number.';
        } elseif (!ctype_digit($stock) || intval($stock) < 0) {
            $error = 'Stock must be a non-negative whole number.';
        } else {
            $image_path = '';
            if (!empty($_FILES['image']['name'])) {
                $allowed  = ['image/jpeg', 'image/png'];
                $max_size = 2 * 1024 * 1024;
                $finfo    = finfo_open(FILEINFO_MIME_TYPE);
                $mime     = finfo_file($finfo, $_FILES['image']['tmp_name']);
                finfo_close($finfo);

                if (!in_array($mime, $allowed)) {
                    $error = 'Image must be JPEG or PNG.';
                } elseif ($_FILES['image']['size'] > $max_size) {
                    $error = 'Image must be under 2MB.';
                } else {
                    $filename = uniqid() . '_' . basename($_FILES['image']['name']);
                    if (!move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $filename)) {
                        $error = 'Failed to upload image.';
                    } else {
                        $image_path = $upload_web . $filename;
                    }
                }
            }

            if ($error === '') {
                if (addMedicine($conn, $name, $category_id, $vendor,
                    floatval($price), intval($stock), $desc, $image_path)) {
                    header('Location: index.php?page=medicines&msg=added');
                    exit;
                }
                $error = 'Failed to add medicine.';
            }
        }
    }

    if ($action === 'edit' && $_SERVER['REQUEST_METHOD'] === 'GET') {
        $id      = intval($_GET['id'] ?? 0);
        $editing = getMedicine($conn, $id);
    }

    if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id          = intval($_GET['id'] ?? 0);
        $name        = trim($_POST['name'] ?? '');
        $category_id = intval($_POST['category_id'] ?? 0);
        $vendor      = trim($_POST['vendor_name'] ?? '');
        $price       = trim($_POST['price'] ?? '');
        $stock       = trim($_POST['availability'] ?? '');
        $desc        = trim($_POST['description'] ?? '');

        if ($name === '' || $category_id === 0 || $vendor === '' || $price === '' || $stock === '') {
            $error   = 'All fields except description are required.';
            $editing = getMedicine($conn, $id);
        } elseif (!is_numeric($price) || floatval($price) <= 0) {
            $error   = 'Price must be a positive number.';
            $editing = getMedicine($conn, $id);
        } elseif (!ctype_digit($stock) || intval($stock) < 0) {
            $error   = 'Stock must be a non-negative whole number.';
            $editing = getMedicine($conn, $id);
        } else {
            $current    = getMedicine($conn, $id);
            $image_path = $current['image_path'];

            if (!empty($_FILES['image']['name'])) {
                $allowed  = ['image/jpeg', 'image/png'];
                $max_size = 2 * 1024 * 1024;
                $finfo    = finfo_open(FILEINFO_MIME_TYPE);
                $mime     = finfo_file($finfo, $_FILES['image']['tmp_name']);
                finfo_close($finfo);

                if (!in_array($mime, $allowed)) {
                    $error   = 'Image must be JPEG or PNG.';
                    $editing = $current;
                } elseif ($_FILES['image']['size'] > $max_size) {
                    $error   = 'Image must be under 2MB.';
                    $editing = $current;
                } else {
                    $filename = uniqid() . '_' . basename($_FILES['image']['name']);
                    $new_path = $upload_web . $filename;
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $filename)) {
                        if ($image_path && file_exists(__DIR__ . '/' . $image_path)) {
                            unlink(__DIR__ . '/' . $image_path);
                        }
                        $image_path = $new_path;
                    } else {
                        $error   = 'Failed to upload image.';
                        $editing = $current;
                    }
                }
            }

            if ($error === '') {
                if (updateMedicine($conn, $id, $name, $category_id, $vendor,
                    floatval($price), intval($stock), $desc, $image_path)) {
                    header('Location: index.php?page=medicines&msg=updated');
                    exit;
                }
                $error = 'Failed to update medicine.';
            }
        }
    }

    if ($action === 'delete') {
        $id     = intval($_GET['id'] ?? 0);
        $result = deleteMedicine($conn, $id);
        if ($result === false) {
            header('Location: index.php?page=medicines&msg=blocked');
        } else {
            header('Location: index.php?page=medicines&msg=deleted');
        }
        exit;
    }

    $medicines  = getAllMedicines($conn);
    $categories = getAllCategories($conn);
    require 'views/medicines.php';
}

// CUSTOMER CONTROLLER (Task 2) 
function customerCtrl($conn) {
    adminGate();
    $action = $_GET['action'] ?? 'list';

    if ($action === 'delete') {
        $id = intval($_GET['id'] ?? 0);
        if ($id > 0) deleteCustomer($conn, $id);
        header('Location: index.php?page=customers&msg=deleted');
        exit;
    }

    $customers = getAllCustomers($conn);
    require 'views/customers.php';
}

// ORDERS CONTROLLER (Task 2) 
function ordersCtrl($conn) {
    adminGate();

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax'])) {
        header('Content-Type: application/json');
        $order_id = intval($_POST['order_id'] ?? 0);
        $status   = $_POST['status'] ?? '';

        if (!in_array($status, ['accepted', 'rejected'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid status.']);
            exit;
        }
        if (updateOrderStatus($conn, $order_id, $status)) {
            echo json_encode(['success' => true, 'message' => 'Order ' . $status . '.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Update failed.']);
        }
        exit;
    }

    $orders = getAllOrders($conn);
    require 'views/orders.php';
}

// PURCHASE HISTORY CONTROLLER (Task 2) 
function historyCtrl($conn) {
    adminGate();
    $orders = getAcceptedOrders($conn);

    $orderItems = [];
    foreach ($orders as $order) {
        $orderItems[$order['id']] = getOrderItems($conn, $order['id']);
    }

    require 'views/purchase_history.php';
}
?>