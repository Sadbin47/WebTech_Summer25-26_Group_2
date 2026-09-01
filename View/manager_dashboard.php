
<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Manager') {
    header("Location: login.php");
    exit;
}

$lastSection = $_COOKIE['manager_last_section'] ?? 'dashboard';

include "header.php";
?>

<style>
.manager-page {
    min-height: calc(100vh - 62px);
    background: #f4f7fb;
    padding: 22px 15px 30px;
    font-family: Arial, sans-serif;
}

.manager-container {
    width: 92%;
    max-width: 1100px;
    margin: auto;
}

/* Welcome */
.welcome-box {
    background: linear-gradient(135deg, #28795c, #1f9d75);
    color: white;
    padding: 20px 24px;
    border-radius: 8px;
    margin-bottom: 22px;
}

.welcome-box h2 {
    margin: 0 0 5px;
    font-size: 26px;
}

.welcome-box p {
    margin: 0;
    font-size: 14px;
}

/* Section */
.section-title {
    margin-bottom: 12px;
}

.section-title h3 {
    margin: 0;
    color: #252b33;
    font-size: 19px;
}

.section-title p {
    margin: 4px 0 0;
    color: #777;
    font-size: 13px;
}

/* Navigation Options */
.dashboard-options {
    background: white;
    border-radius: 8px;
    overflow: hidden;
    border: 1px solid #e1e5e9;
}

.dashboard-option {
    display: flex;
    align-items: center;
    padding: 16px 20px;
    text-decoration: none;
    color: #252b33;
    border-bottom: 1px solid #e5e8eb;
    transition: 0.2s;
}

.dashboard-option:last-child {
    border-bottom: none;
}

.dashboard-option:hover {
    background: #f5f9f7;
    padding-left: 25px;
}

/* Icon */
.option-icon {
    width: 42px;
    font-size: 24px;
    text-align: center;
    margin-right: 15px;
}

/* Text */
.option-content {
    flex: 1;
}

.option-content h4 {
    margin: 0 0 3px;
    font-size: 16px;
}

.option-content p {
    margin: 0;
    color: #777;
    font-size: 13px;
}

/* Arrow */
.option-arrow {
    font-size: 20px;
    color: #28795c;
}

/* Different colors */
.employee-option .option-icon {
    color: #28795c;
}

.product-option .option-icon {
    color: #2879b9;
}

.information-option .option-icon {
    color: #d28a24;
}

/* Last Section */
.last-section {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 18px;
    padding: 12px 16px;
    background: white;
    border-left: 4px solid #28795c;
    border-radius: 6px;
}

.last-section-label {
    color: #777;
    font-size: 13px;
}

.last-section-value {
    color: #28795c;
    font-size: 13px;
    font-weight: bold;
}

/* Responsive */
@media (max-width: 600px) {
    .manager-page {
        padding: 15px 10px 25px;
    }

    .manager-container {
        width: 95%;
    }

    .welcome-box {
        padding: 17px 18px;
    }

    .welcome-box h2 {
        font-size: 22px;
    }

    .dashboard-option {
        padding: 14px;
    }

    .option-content p {
        display: none;
    }

    .last-section {
        flex-direction: column;
        align-items: flex-start;
        gap: 5px;
    }
}
</style>

<div class="manager-page">

    <div class="manager-container">

        <!-- Welcome -->
        <div class="welcome-box">

            <h2>Manager Dashboard</h2>

            <p>
                Welcome back,
                <strong>
                    <?php echo htmlspecialchars($_SESSION['name'] ?? 'Manager'); ?>
                </strong>.
                Manage your operations from here.
            </p>

        </div>


        <!-- Management Options -->
        <div class="section-title">

            <h3>Management Options</h3>

            <p>
                Select an option to continue.
            </p>

        </div>


        <!-- Options -->
        <div class="dashboard-options">

            <a href="manage_employees.php"
               class="dashboard-option employee-option">

                <div class="option-icon">
                    👥
                </div>

                <div class="option-content">

                    <h4>Manage Employees</h4>

                    <p>
                        Add, update and manage employee records.
                    </p>

                </div>

                <div class="option-arrow">
                    →
                </div>

            </a>


            <a href="manage_product.php"
               class="dashboard-option product-option">

                <div class="option-icon">
                    📦
                </div>

                <div class="option-content">

                    <h4>Manage Product</h4>

                    <p>
                        Manage products and inventory information.
                    </p>

                </div>

                <div class="option-arrow">
                    →
                </div>

            </a>


            <a href="update_manager.php"
               class="dashboard-option information-option">

                <div class="option-icon">
                    👤
                </div>

                <div class="option-content">

                    <h4>My Information</h4>

                    <p>
                        View and update your manager information.
                    </p>

                </div>

                <div class="option-arrow">
                    →
                </div>

            </a>

        </div>


        <!-- Last Visited -->
        <div class="last-section">

            <span class="last-section-label">
                Last visited section
            </span>

            <span class="last-section-value">
                <?php echo htmlspecialchars($lastSection); ?>
            </span>

        </div>

    </div>

</div>

<script>
document.cookie =
    "manager_last_section=dashboard; max-age=86400; path=/";
</script>

