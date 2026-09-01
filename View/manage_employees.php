
<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Manager') {
    header("Location: login.php");
    exit;
}

include "header.php";
?>

<style>
    .employee-page {
        width: 90%;
        max-width: 1100px;
        margin: 30px auto;
        font-family: Arial, sans-serif;
    }

    .page-title {
        margin-bottom: 5px;
        font-size: 26px;
        color: #222;
    }

    .page-subtitle {
        margin: 0 0 25px;
        color: #666;
        font-size: 14px;
    }

    .management-section {
        border-top: 2px solid #222;
        padding-top: 20px;
    }

    .section-title {
        font-size: 18px;
        margin-bottom: 15px;
        color: #333;
    }

    .options {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
    }

    .action-button {
        display: inline-block;
        padding: 11px 18px;
        background: #222;
        color: white;
        text-decoration: none;
        border: 1px solid #222;
        font-size: 14px;
        cursor: pointer;
        transition: 0.2s ease;
    }

    .action-button:hover {
        background: white;
        color: #222;
    }

    @media (max-width: 600px) {
        .employee-page {
            width: 94%;
            margin-top: 20px;
        }

        .options {
            flex-direction: column;
        }

        .action-button {
            width: 100%;
            box-sizing: border-box;
            text-align: center;
        }
    }
</style>

<div class="employee-page">
    <h2 class="page-title">Manage Employees</h2>

    <p class="page-subtitle"></p>

    <div class="management-section">

        <div class="section-title">
            Employee Operations
        </div>

        <div class="options">
            <a href="add_employee.php" class="action-button">
                Add Employee
            </a>

            <a href="delete_employee.php" class="action-button">
                Delete Employee
            </a>
        </div>

    </div>
</div>

<script>
document.cookie = "manager_last_section=employees; max-age=86400; path=/";
</script>

