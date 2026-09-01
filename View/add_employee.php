
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
        max-width: 700px;
        margin: 30px auto;
        font-family: Arial, sans-serif;
    }

    .page-title {
        margin: 0 0 5px;
        font-size: 26px;
        color: #222;
    }

    .page-subtitle {
        margin: 0 0 25px;
        color: #666;
        font-size: 14px;
    }

    .form-section {
        border-top: 2px solid #222;
        padding-top: 20px;
    }

    .section-title {
        font-size: 18px;
        color: #333;
        margin-bottom: 20px;
    }

    .form-group {
        margin-bottom: 17px;
    }

    .form-group label {
        display: block;
        margin-bottom: 6px;
        font-size: 14px;
        font-weight: bold;
        color: #333;
    }

    .form-group input {
        width: 100%;
        padding: 10px;
        box-sizing: border-box;
        border: 1px solid #aaa;
        font-size: 14px;
        outline: none;
    }

    .form-group input:focus {
        border-color: #222;
    }

    .submit-button {
        margin-top: 5px;
        padding: 11px 20px;
        background: #222;
        color: white;
        border: 1px solid #222;
        font-size: 14px;
        cursor: pointer;
        transition: 0.2s ease;
    }

    .submit-button:hover {
        background: white;
        color: #222;
    }

    #message {
        margin-top: 15px;
        font-size: 14px;
    }

    @media (max-width: 600px) {
        .employee-page {
            width: 94%;
            margin-top: 20px;
        }
    }
</style>

<div class="employee-page">

    <h2 class="page-title">Add Employee</h2>

    <p class="page-subtitle">
        Enter the employee information 
    </p>

    <div class="form-section">

        <div class="section-title">
            Employee Information
        </div>

        <form id="employeeForm">

            <div class="form-group">
                <label for="name">Name</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    placeholder="Enter employee name"
                    required
                >
            </div>

            <div class="form-group">
                <label for="username">Username</label>
                <input
                    type="text"
                    id="username"
                    name="username"
                    placeholder="Enter username"
                    required
                >
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Enter password"
                    required
                >
            </div>

            <button type="submit" class="submit-button">
                Add Employee
            </button>

        </form>

        <p id="message"></p>

    </div>

</div>
<script>
document.getElementById("employeeForm").addEventListener("submit", function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const message = document.getElementById("message");

    fetch("../Controller/ManagerController.php?action=add_employee", {
        method: "POST",
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        message.innerText = data.message;

        if (data.success) {
            this.reset();
        }
    })
    .catch(() => {
        message.innerText = "Something went wrong.";
    });
});
</script>

