<?php
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
                $allowed     = ['image/jpeg', 'image/png'];
                $max_size    = 2 * 1024 * 1024; // 2MB
                $finfo       = finfo_open(FILEINFO_MIME_TYPE);
                $mime        = finfo_file($finfo, $_FILES['image']['tmp_name']);
                finfo_close($finfo);

                if (!in_array($mime, $allowed)) {
                    $error = 'Image must be JPEG or PNG.';
                } elseif ($_FILES['image']['size'] > $max_size) {
                    $error = 'Image must be under 2MB.';
                } else {
                    $upload_dir = 'public/uploads/medicines/';
                    if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
                    $filename   = uniqid() . '_' . basename($_FILES['image']['name']);
                    $image_path = $upload_dir . $filename;
                    if (!move_uploaded_file($_FILES['image']['tmp_name'], $image_path)) {
                        $error      = 'Failed to upload image.';
                        $image_path = '';
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
            
            $current     = getMedicine($conn, $id);
            $image_path  = $current['image_path'];

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
                    $upload_dir = 'public/uploads/medicines/';
                    if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
                    $filename   = uniqid() . '_' . basename($_FILES['image']['name']);
                    $new_path   = $upload_dir . $filename;
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $new_path)) {
                        
                        if ($image_path && file_exists($image_path)) unlink($image_path);
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