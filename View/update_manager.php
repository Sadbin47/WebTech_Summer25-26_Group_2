
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

$managerId = (int)($_SESSION['user_id'] ?? 0);
$manager = $model->getManager($managerId);

if (!$manager) {
    exit("Manager information not found.");
}

include "header.php";
?>

<div class="profile-container">

    <div class="profile-header">
        <h2>My Information</h2>
    </div>

    <form id="managerForm">

        <div class="form-group">
            <label>Username</label>
            <input
                type="text"
                name="username"
                value="<?php echo htmlspecialchars($manager['username']); ?>"
                required
            >
        </div>

        <div class="form-group">
            <label>Password</label>
            <input
                type="password"
                name="password"
                placeholder="Enter new password"
            >
            <small>Blank:IF UPDATE IS NOT NEEDED</small>
        </div>

        <div class="form-group">
            <label>Full Name</label>
            <input
                type="text"
                name="name"
                value="<?php echo htmlspecialchars($manager['name']); ?>"
                required
            >
        </div>

        <button type="submit" class="update-btn">
            Update Information
        </button>
    </form>
    <p id="message"></p>
</div>

<script>
document.getElementById("managerForm").addEventListener("submit", function(e) {

    e.preventDefault();

    const formData = new FormData(this);

    fetch("../Controller/ManagerController.php?action=update_manager", {
        method: "POST",
        body: formData
    })
    .then(response => response.json())
    .then(data => {

        const message = document.getElementById("message");

        message.innerText = data.message;

        if (data.success) {
            message.className = "success-message";

            setTimeout(() => {
                location.reload();
            }, 800);
        } else {
            message.className = "error-message";
        }

    })
    .catch(error => {

        const message = document.getElementById("message");

        message.innerText = "Something went wrong. Please try again.";
        message.className = "error-message";

    });
});
</script>

<style>
.profile-container {
    width: 650px;
    max-width: 90%;
    margin: 30px auto;
    font-family: Arial, sans-serif;
}
.profile-header {
    margin-bottom: 18px;
}
.profile-header h2 {
    margin: 0;
    font-size: 24px;
}
#managerForm {
    border: 1px solid #ddd;
    padding: 25px;
    background: #fff;
}
.form-group {
    margin-bottom: 16px;
}
.form-group label {
    display: block;
    margin-bottom: 6px;
    font-weight: bold;
    font-size: 14px;
}
.form-group input {
    width: 100%;
    box-sizing: border-box;
    padding: 10px;
    border: 1px solid #bbb;
    font-size: 14px;
}
.form-group input:focus {
    outline: none;
    border-color: #333;
}
.form-group small {
    display: block;
    margin-top: 5px;
    color: #777;
    font-size: 12px;
}
.update-btn {
    padding: 10px 20px;
    border: none;
    background: #222;
    color: white;
    cursor: pointer;
    font-size: 14px;
}
.update-btn:hover {
    background: #444;
}
#message {
    margin-top: 15px;
    font-size: 14px;
}
.success-message {
    color: green;
}
.error-message {
    color: red;
}
</style>

