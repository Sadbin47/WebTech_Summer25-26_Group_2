<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Manager') {
    header("Location: login.php");
    exit;
}

require_once "../Model/db.php";
require_once "../Model/ManagerModel.php";

$database = new Database();
$connection = $database->connect();
$model = new ManagerModel($connection);

$employees = $model->getEmployees();

include "header.php";
?>

<style>
    .delete-container {
        width: 90%;
        max-width: 1100px;
        margin: 25px auto;
        font-family: Arial, sans-serif;
    }

    .page-header {
        margin-bottom: 18px;
        border-bottom: 1px solid #ddd;
        padding-bottom: 12px;
    }

    .page-header h2 {
        margin: 0;
        font-size: 24px;
    }

    .page-header p {
        margin: 5px 0 0;
        color: #666;
        font-size: 14px;
    }

    .employee-table {
        width: 100%;
        border-collapse: collapse;
        background: #fff;
    }

    .employee-table th,
    .employee-table td {
        border: 1px solid #ddd;
        padding: 10px 12px;
        text-align: left;
    }

    .employee-table th {
        background: #f5f5f5;
        font-weight: 600;
    }

    .employee-table tr:hover {
        background: #fafafa;
    }

    .delete-btn {
        padding: 6px 14px;
        border: 1px solid #c62828;
        background: #d32f2f;
        color: white;
        border-radius: 3px;
        cursor: pointer;
        font-size: 13px;
    }

    .delete-btn:hover {
        background: #b71c1c;
    }

    .message {
        margin-top: 15px;
        font-size: 14px;
        font-weight: 500;
    }

    .empty-message {
        text-align: center;
        padding: 20px;
        color: #777;
        border: 1px solid #ddd;
    }
</style>

<div class="delete-container">

    <div class="page-header">
        <h2>Delete Employees</h2>
        <p>Select an employee below to remove them from the system.</p>
    </div>

    <?php if (!empty($employees)): ?>

        <table class="employee-table">
            <thead>
                <tr>
                    <th width="10%">ID</th>
                    <th width="35%">Name</th>
                    <th width="35%">Username</th>
                    <th width="20%">Action</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($employees as $employee): ?>

                    <tr id="row-<?php echo $employee['id']; ?>">
                        <td>
                            <?php echo $employee['id']; ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($employee['name']); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($employee['username']); ?>
                        </td>

                        <td>
                            <button
                                type="button"
                                class="delete-btn"
                                onclick="deleteEmployee(<?php echo $employee['id']; ?>)"
                            >
                                Delete
                            </button>
                        </td>
                    </tr>

                <?php endforeach; ?>
            </tbody>
        </table>

    <?php else: ?>

        <div class="empty-message">
            No employees found.
        </div>

    <?php endif; ?>

    <p id="message" class="message"></p>

</div>

<script>
function deleteEmployee(id) {

    const confirmed = confirm(
        "Are you sure you want to delete this employee?"
    );

    if (!confirmed) {
        return;
    }

    const formData = new FormData();
    formData.append("id", id);

    fetch("../Controller/ManagerController.php?action=delete_employee", {
        method: "POST",
        body: formData
    })
    .then(response => response.json())
    .then(data => {

        const message = document.getElementById("message");
        message.innerText = data.message;

        if (data.success) {
            const row = document.getElementById("row-" + id);

            if (row) {
                row.remove();
            }
        }
    })
    .catch(error => {
        document.getElementById("message").innerText =
            "Something went wrong. Please try again.";
    });
}
</script>