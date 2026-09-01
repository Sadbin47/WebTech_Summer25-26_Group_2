<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "../Model/db.php";
require_once "../Model/ManagerModel.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Manager') {
    if (
        isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
    ) {
        header('Content-Type: application/json');

        echo json_encode([
            'success' => false, 'message' => 'Unauthorized access' ]);

        exit;
    }

    header("Location: ../View/login.php");
    exit;
}

$database = new Database();
$connection = $database->connect();
$model = new ManagerModel($connection);

$action = $_GET['action'] ?? '';

// ADD EMPLOYEE
if ($action === 'add_employee') {
    header('Content-Type: application/json');

    $name = trim($_POST['name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($name === '' || $username === '' || $password === '') {
        echo json_encode([ 'success' => false, 'message' => 'All fields are required']);
        exit;
    }

    $result = $model->addEmployee($name, $username, $password);

    echo json_encode(['success' => $result, 'message' => $result  ? 'Employee added successfully'
    : 'Username already exists']);

    exit;
}

// DELETE EMPLOYEE
if ($action === 'delete_employee') {
    header('Content-Type: application/json');

    $id = (int)($_POST['id'] ?? 0);

    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid employee ID'
        ]);
        exit;
    }

    $result = $model->deleteEmployee($id);

    echo json_encode(['success' => $result, 'message' => $result
            ? 'Employee deleted successfully'
            : 'Employee could not be deleted']);

    exit;
}

// UPDATE EMPLOYEE
if ($action === 'update_employee') {
    header('Content-Type: application/json');

    $id = (int)($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $username = trim($_POST['username'] ?? '');

    if ($id <= 0 || $name === '' || $username === '') {
        echo json_encode([ 'success' => false, 'message' => 'All fields are required'
        ]);
        exit;
    }

    $result = $model->updateEmployee($id, $name, $username);

    echo json_encode(['success' => $result,'message' => $result
           ? 'Employee information updated'   : 'Update failed' ]);

    exit;
}

// ADD PRODUCT
if ($action === 'add_product') {
    header('Content-Type: application/json');

    $name = trim($_POST['name'] ?? '');
    $size = trim($_POST['size'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $quantity = (int)($_POST['quantity'] ?? 0);

    if (
        $name === '' ||
        $size === '' ||
        $category === '' ||
        $price <= 0 ||
        $quantity < 0
    ) {
        echo json_encode(['success' => false, 'message' => 'Please enter valid product information'
        ]);
        exit;
    }

    $result = $model->addProduct($name, $size, $category, $price, $quantity);

    echo json_encode([ 'success' => $result, 'message' => $result
       ? 'Product added successfully'  : 'Product could not be added']);

    exit;
}

// UPDATE PRODUCT
if ($action === 'update_product') {
    header('Content-Type: application/json');

    $id = (int)($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $size = trim($_POST['size'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $quantity = (int)($_POST['quantity'] ?? 0);

    if (
        $id <= 0 ||
        $name === '' ||
        $size === '' ||
        $category === '' ||
        $price <= 0 ||
        $quantity < 0
    ) {
        echo json_encode(['success' => false, 'message' => 'Invalid product information'
        ]);
        exit;
    }

    $result = $model->updateProduct(
        $id,
        $name,
        $size,
        $category,
        $price,
        $quantity
    );

    echo json_encode(['success' => $result,'message' => $result
            ? 'Product updated successfully'   : 'Product update failed' ]);

    exit;
}

// DELETE PRODUCT
if ($action === 'delete_product') {
    header('Content-Type: application/json');

    $id = (int)($_POST['id'] ?? 0);

    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
        exit;
    }

    $result = $model->deleteProduct($id);

    echo json_encode([ 'success' => $result,'message' => $result
            ? 'Product deleted successfully': 'Product could not be deleted']);

    exit;
}

// UPDATE MANAGER
if ($action === 'update_manager') {
    header('Content-Type: application/json');

    $id = (int)($_SESSION['user_id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($id <= 0 || $name === '' || $username === '') {
        echo json_encode([
            'success' => false,
            'message' => 'Name and username are required'
        ]);
        exit;
    }

    try {

        if ($password !== '') {

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $result = $model->updateManager(
                $id,
                $name,
                $username,
                $hashedPassword
            );

        } else {

            $result = $model->updateManager(
                $id,
                $name,
                $username
            );
        }

        if ($result) {

            $_SESSION['name'] = $name;
            $_SESSION['username'] = $username;

            echo json_encode([
                'success' => true,
                'message' => 'Your information has been updated'
            ]);

        } else {

            echo json_encode([
                'success' => false,
                'message' => 'Update failed'
            ]);
        }

    } catch (PDOException $e) {

        echo json_encode([
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ]);
    }

    exit;
}
