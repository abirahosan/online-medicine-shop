<?php
/* ==================== LOGIN (placeholder for Task 1) ==================== */
function loginCtrl($conn) {
    if (isset($_SESSION['user'])) {
        header('Location: index.php?page=dashboard');
        exit;
    }

    $error = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = trim($_POST['email'] ?? '');
        $pass  = $_POST['password'] ?? '';

        $stmt = mysqli_prepare($conn,
            "SELECT id, name, email, password_hash, role FROM users WHERE email = ?");
        mysqli_stmt_bind_param($stmt, 's', $email);
        mysqli_stmt_execute($stmt);
        $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);

        if ($row && password_verify($pass, $row['password_hash'])) {
            $_SESSION['user'] = [
                'id'   => $row['id'],
                'name' => $row['name'],
                'role' => $row['role']
            ];
            header('Location: index.php?page=dashboard');
            exit;
        }
        $error = 'Invalid email or password.';
    }

    echo '<!DOCTYPE html><html><head>
        <link rel="stylesheet" href="style.css">
        </head><body style="display:flex;align-items:center;justify-content:center;height:100vh;">
        <div class="card" style="width:360px;">
            <h2 class="card-title">MediShop Login</h2>
            ' . (!empty($error) ? '<div class="alert alert-error">' . htmlspecialchars($error) . '</div>' : '') . '
            <form method="POST" class="form" style="margin-top:16px;">
                <div class="field">
                    <label>Email</label>
                    <input type="email" name="email" placeholder="admin@medicine.com" required>
                </div>
                <div class="field">
                    <label>Password</label>
                    <input type="password" name="password" placeholder="Password" required>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary" style="width:100%;">Login</button>
                </div>
            </form>
        </div>
    </body></html>';
}

/* ==================== REGISTER (placeholder for Task 1) ==================== */
function registerCtrl($conn) {
    // This will be implemented by Task 1 (22-48988-3)
    echo '<!DOCTYPE html><html><body>
        <h2>Register page - To be implemented by Task 1</h2>
    </body></html>';
}

//admin gate
function adminGate() {
    if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
        header('Location: index.php?page=login');
        exit;
    }
}

//dashboard
function dashboardCtrl($conn) {
    adminGate();
    $counts = getDashboardCounts($conn);
    require 'views/admin_dashboard.php';
}

//category controller
function categoryCtrl($conn) {
    adminGate();
    $action  = $_GET['action'] ?? 'list';
    $error   = '';
    $editing = null;

    //add
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

    //edit
    if ($action === 'edit' && $_SERVER['REQUEST_METHOD'] === 'GET') {
        $id      = intval($_GET['id'] ?? 0);
        $editing = getCategory($conn, $id);
    }

    //update
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

    //delete
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

//medicine controller
function medicineCtrl($conn) {
    adminGate();
    $action  = $_GET['action'] ?? 'list';
    $error   = '';
    $editing = null;

    $upload_dir = __DIR__ . '/public/uploads/medicines/';
    $upload_web = 'public/uploads/medicines/';

    //add
    if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $name        = trim($_POST['name'] ?? '');
        $category_id = intval($_POST['category_id'] ?? 0);
        $vendor      = trim($_POST['vendor_name'] ?? '');
        $price       = trim($_POST['price'] ?? '');
        $stock       = trim($_POST['availability'] ?? '');
        $desc        = trim($_POST['description'] ?? '');

        //php validation
        if ($name === '' || $category_id === 0 || $vendor === '' || $price === '' || $stock === '') {
            $error = 'All fields except description are required.';
        } elseif (!is_numeric($price) || floatval($price) <= 0) {
            $error = 'Price must be a positive number.';
        } elseif (!ctype_digit($stock) || intval($stock) < 0) {
            $error = 'Stock must be a non-negative whole number.';
        } else {
            //image upload
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

    //edit
    if ($action === 'edit' && $_SERVER['REQUEST_METHOD'] === 'GET') {
        $id      = intval($_GET['id'] ?? 0);
        $editing = getMedicine($conn, $id);
    }

    //update
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

    //delete
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

//customer controller
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

//orders controller
function ordersCtrl($conn) {
    adminGate();

    //ajax
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

//purchase history controller
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