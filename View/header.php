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
        'Dashboard' => 'manager_dashboard.php#dashboard',
        'Inventory' => 'manager_dashboard.php#inventory',
        'Reports' => 'manager_dashboard.php#reports'
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
        'Products' => 'customer_dashboard.php#products',
        'Cart' => 'customer_dashboard.php#cart',
        'Checkout' => 'customer_dashboard.php#checkout',
        'Orders' => 'customer_dashboard.php#orders'
    ];
} else {
    $homePage = 'login.php';
    $menu = ['Login' => 'login.php', 'Register' => 'register.php'];
}
?>

<style>
    .app-header {
        background: #252b33;
        color: white;
        font-family: Arial, sans-serif;
    }

    .app-header-content {
        display: flex;
        align-items: center;
        width: 1000px;
        height: 60px;
        margin: 0 auto;
    }

    .app-header a {
        color: #e6e9ec;
        text-decoration: none;
    }

    .app-brand {
        font-size: 18px;
        font-weight: bold;
    }

    .app-menu {
        display: flex;
        flex: 1;
        margin-left: 25px;
    }

    .app-menu a {
        padding: 10px;
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
        gap: 10px;
    }

    .app-role {
        color: #aeb7c0;
        font-size: 12px;
    }

    .app-logout {
        padding: 7px 10px;
        background: #b94747;
        color: white;
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
