<?php

session_start();

require_once '../Model/db.php';
require_once '../Model/UserModel.php';

if (($_SESSION['role'] ?? '') !== 'Admin') {
    header('Location: ../View/login.php');
    exit;
}

$isAjax = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest'
    || isset($_POST['ajax']);

function returnToDashboard(string $message, bool $error = false, string $section = 'dashboard'): void
{
    global $isAjax;

    if ($isAjax) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => !$error,
            'message' => $message,
            'section' => $section
        ]);
        exit;
    }

    $_SESSION[$error ? 'admin_error' : 'admin_message'] = $message;
    header('Location: ../View/admin_dashboard.php?section=' . urlencode($section));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../View/admin_dashboard.php');
    exit;
}

$database = new Database();
$userModel = new UserModel($database->connect());
$action = $_POST['action'] ?? '';
$roles = ['Admin', 'Manager', 'Salesman', 'Customer'];

try {
    if ($action === 'create_user') {
        $name = trim($_POST['name'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? '';

        if (strlen($name) < 2 || strlen($username) < 3 || strlen($password) < 5 || !in_array($role, $roles, true)) {
            returnToDashboard('Please provide valid user information.', true, 'users');
        }

        $userModel->createUser($name, $username, $password, $role);
        returnToDashboard('User added successfully.', false, 'users');
    }

    if ($action === 'update_role') {
        $userId = (int) ($_POST['user_id'] ?? 0);
        $role = $_POST['role'] ?? '';

        if ($userId === (int) $_SESSION['user_id'] || !in_array($role, $roles, true)) {
            returnToDashboard('You cannot change your own Admin role.', true, 'users');
        }

        $userModel->updateRole($userId, $role);
        returnToDashboard('User role updated.', false, 'users');
    }

    if ($action === 'delete_user') {
        $userId = (int) ($_POST['user_id'] ?? 0);

        if ($userId === (int) $_SESSION['user_id']) {
            returnToDashboard('Use Delete Profile to remove your own account.', true, 'users');
        }

        $userModel->deleteUser($userId);
        returnToDashboard('User deleted.', false, 'users');
    }

    if ($action === 'save_settings') {
        $taxRate = (float) ($_POST['tax_rate'] ?? 0);
        $shippingZone = $_POST['shipping_zone'] ?? '';
        $zones = ['Inside Dhaka', 'Outside Dhaka'];

        if ($taxRate < 0 || $taxRate > 100 || !in_array($shippingZone, $zones, true)) {
            returnToDashboard('Invalid system settings.', true, 'settings');
        }

        $userModel->saveSettings($taxRate, $shippingZone);
        returnToDashboard('System settings saved.', false, 'settings');
    }

    if ($action === 'update_profile') {
        $name = trim($_POST['name'] ?? '');
        $username = trim($_POST['username'] ?? '');

        if (strlen($name) < 2 || strlen($username) < 3) {
            returnToDashboard('Name or username is too short.', true, 'settings');
        }

        $userModel->updateProfile((int) $_SESSION['user_id'], $name, $username);
        $_SESSION['name'] = $name;
        $_SESSION['username'] = $username;
        returnToDashboard('Profile updated.', false, 'settings');
    }

    if ($action === 'change_password') {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $admin = $userModel->findById((int) $_SESSION['user_id']);

        if (!$admin || !password_verify($currentPassword, $admin['password'])) {
            returnToDashboard('Current password is incorrect.', true, 'settings');
        }

        if (strlen($newPassword) < 5) {
            returnToDashboard('New password must be at least 5 characters.', true, 'settings');
        }

        $userModel->updatePassword((int) $_SESSION['user_id'], $newPassword);
        returnToDashboard('Password changed successfully.', false, 'settings');
    }

    if ($action === 'delete_profile') {
        $password = $_POST['password'] ?? '';
        $admin = $userModel->findById((int) $_SESSION['user_id']);

        if (!$admin || !password_verify($password, $admin['password'])) {
            returnToDashboard('Password confirmation is incorrect.', true, 'settings');
        }

        $userModel->deleteUser((int) $_SESSION['user_id']);
        $_SESSION = [];
        session_destroy();

        if ($isAjax) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => true, 'message' => 'Profile deleted.']);
            exit;
        }

        header('Location: ../View/login.php');
        exit;
    }
} catch (PDOException $exception) {
    $errorSection = in_array($action, ['create_user', 'update_role', 'delete_user'], true)
        ? 'users'
        : 'settings';
    returnToDashboard('Username already exists or the database request failed.', true, $errorSection);
}

returnToDashboard('Unknown action.', true);
