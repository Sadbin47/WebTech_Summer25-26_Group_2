<?php

session_start();

require_once '../Model/db.php';
require_once '../Model/UserModel.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'logout') {
    $_SESSION = [];
    session_destroy();
    header('Location: ../View/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'register') {
    $name = trim($_POST['name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $role = 'Customer';

    if (strlen($name) < 2 || strlen($username) < 3 || strlen($password) < 5) {
        $_SESSION['register_error'] = 'Please provide valid registration information.';
        header('Location: ../View/register.php');
        exit;
    }

    if ($password !== $confirmPassword) {
        $_SESSION['register_error'] = 'Passwords must match.';
        header('Location: ../View/register.php');
        exit;
    }

    try {
        $database = new Database();
        $userModel = new UserModel($database->connect());
        $userModel->createUser($name, $username, $password, $role);
        $_SESSION['login_message'] = 'Registration successful. You can now log in.';
        header('Location: ../View/login.php');
        exit;
    } catch (PDOException $exception) {
        $_SESSION['register_error'] = 'That username is already registered.';
        header('Location: ../View/register.php');
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || $action !== 'login') {
    header('Location: ../View/login.php');
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if ($username === '' || $password === '') {
    $_SESSION['login_error'] = 'Username and password are required.';
    header('Location: ../View/login.php');
    exit;
}

$database = new Database();
$userModel = new UserModel($database->connect());
$user = $userModel->findByUsername($username);

if (!$user || !password_verify($password, $user['password'])) {
    $_SESSION['login_error'] = 'Invalid username or password.';
    header('Location: ../View/login.php');
    exit;
}

session_regenerate_id(true);
$_SESSION['user_id'] = $user['id'];
$_SESSION['name'] = $user['name'];
$_SESSION['username'] = $user['username'];
$_SESSION['role'] = $user['role'];

if (isset($_POST['remember'])) {
    setcookie('remember_user', $user['username'], time() + (86400 * 7), '/');
} else {
    setcookie('remember_user', '', time() - 3600, '/');
}

$dashboardByRole = [
    'Admin' => '../View/admin_dashboard.php',
    'Manager' => '../View/manager_dashboard.php',
    'Salesman' => '../View/salesman_dashboard.php',
    'Customer' => '../View/customer_dashboard.php'
];

header('Location: ' . ($dashboardByRole[$user['role']] ?? '../View/login.php'));
exit;
