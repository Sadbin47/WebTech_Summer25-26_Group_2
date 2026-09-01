<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$headerRole = $_SESSION['role'] ?? 'Guest';
$headerName = htmlspecialchars($_SESSION['name'] ?? $headerRole);

if ($headerRole === 'Admin') {
    $homePage = 'admin_dashboard.php';
    $menu = [
        'Dashboard' => 'admin_dashboard.php?section=dashboard',
        'Users' => 'admin_dashboard.php?section=users',
        'Settings' => 'admin_dashboard.php?section=settings'
    ];
} elseif ($headerRole === 'Manager') {
    $homePage = 'manager_dashboard.php';
    $menu = [
        'Dashboard' => 'manager_dashboard.php',
        ' Employees' => 'manage_employees.php',
        'Product' => 'manage_product.php',
        'My Information' => 'update_manager.php'
    ];
} elseif ($headerRole === 'Salesman') {
    $homePage = 'salesman_dashboard.php';
    $menu = [
        'Sell Dashboard' => 'salesman_dashboard.php#sale',
        'Sales History' => 'salesman_dashboard.php#history'
    ];
} elseif ($headerRole === 'Customer') {
    $homePage = 'customer_dashboard.php';
    $menu = [
        'Products' => 'customer_dashboard.php',
        'Orders' => 'order_history.php'
    ];
} else {
    $homePage = 'login.php';
    $menu = ['Login' => 'login.php', 'Register' => 'register.php'];
}
?>

<style>
    .app-header {
        position: fixed;
        top: 0;
        right: 0;
        left: 0;
        z-index: 1000;
        height: 62px;
        padding: 0 22px;
        background: #252b33;
        color: white;
        font-family: Arial, sans-serif;
    }

    .app-header-content {
        display: flex;
        max-width: 1200px;
        height: 100%;
        margin: auto;
        align-items: center;
        gap: 24px;
    }

    .app-header a {
        margin: 0;
        color: #e6e9ec;
        font-weight: normal;
        text-decoration: none;
    }

    .app-brand {
        font-size: 18px;
        font-weight: bold !important;
        white-space: nowrap;
    }

    .app-menu {
        display: flex;
        flex: 1;
        gap: 4px;
        overflow-x: auto;
    }

    .app-menu a {
        padding: 9px 10px;
        white-space: nowrap;
    }

    .app-menu a:hover {
        background: #3b444f;
        color: white;
    }

    .app-menu a.active {
        background: #28795c;
        color: white;
    }

    .app-user {
        display: flex;
        align-items: center;
        gap: 12px;
        white-space: nowrap;
    }

    .app-role {
        color: #aeb7c0;
        font-size: 12px;
    }

    .app-logout {
        padding: 7px 10px;
        background: #b94747;
        color: white !important;
    }

    .app-header-space {
        width: 100%;
        height: 62px;
        flex-shrink: 0;
    }

    [id] {
        scroll-margin-top: 75px;
    }

    @media (max-width: 650px) {
        .app-header {
            padding: 0 10px;
        }

        .app-header-content {
            gap: 8px;
        }

        .app-user-name {
            display: none;
        }
    }
</style>

<header class="app-header">
    <div class="app-header-content">
        <a class="app-brand" href="<?php echo $homePage; ?>">JerseyTrack</a>

        <nav class="app-menu">
            <?php foreach ($menu as $label => $link): ?>
                <?php
                $active = '';
                if ($headerRole === 'Admin') {
                    $currentSection = $_GET['section'] ?? 'dashboard';
                    $active = str_contains($link, 'section=' . $currentSection) ? 'active' : '';
                }
                ?>
                <a class="<?php echo $active; ?>" href="<?php echo htmlspecialchars($link); ?>"><?php echo htmlspecialchars($label); ?></a>
            <?php endforeach; ?>
        </nav>

        <?php if ($headerRole !== 'Guest'): ?>
            <div class="app-user">
                <span class="app-user-name">
                    <?php echo $headerName; ?> <span class="app-role"><?php echo htmlspecialchars($headerRole); ?></span>
                </span>
                <a class="app-logout" href="../Controller/AuthController.php?action=logout">Logout</a>
            </div>
        <?php endif; ?>
    </div>
</header>

<div class="app-header-space" aria-hidden="true"></div>
