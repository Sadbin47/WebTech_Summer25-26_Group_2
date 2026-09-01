<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (($_SESSION['role'] ?? '') !== 'Admin') {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../Model/db.php';
require_once __DIR__ . '/../Model/UserModel.php';

$userModel = new UserModel((new Database())->connect());
$users = $userModel->getAllUsers();
$settings = $userModel->getSettings();
$revenue = $userModel->getRevenueSummary();
$admin = $userModel->findById((int) $_SESSION['user_id']);
$adminName = htmlspecialchars($_SESSION['name'] ?? 'Admin');
$section = $_GET['section'] ?? 'dashboard';

if (!in_array($section, ['dashboard', 'users', 'settings'], true)) {
    $section = 'dashboard';
}

$message = $_SESSION['admin_message'] ?? '';
$error = $_SESSION['admin_error'] ?? '';
unset($_SESSION['admin_message'], $_SESSION['admin_error']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <style>
        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            color: #333333;
        }

        .main {
            width: 1000px;
            margin: 25px auto;
        }

        .welcome {
            margin-bottom: 20px;
            padding: 18px;
            border-bottom: 4px solid #28795c;
            background: #ffffff;
            color: #245a45;
            overflow: hidden;
        }

        .welcome h1,
        .welcome p { margin: 0 0 5px; }

        .welcome p { color: #666666; }

        .welcome-badge {
            float: right;
            padding: 10px 14px;
            background: #28795c;
            color: #ffffff;
            font-size: 22px;
            font-weight: bold;
        }

        .stats,
        .quick-links { margin-bottom: 18px; }

        .stat,
        .quick-link {
            display: inline-block;
            width: 32%;
            min-height: 85px;
            margin-right: 1%;
            padding: 14px;
            border: 1px solid #d2d2d2;
            background: #ffffff;
            vertical-align: top;
        }

        .stat:last-child,
        .quick-link:last-child { margin-right: 0; }

        .stat { text-align: center; }

        .stat span,
        .quick-link span,
        .user small {
            color: #666666;
            font-size: 13px;
        }

        .stat strong,
        .quick-link strong {
            display: block;
            margin-top: 6px;
            color: #333333;
            font-size: 19px;
        }

        .quick-link {
            color: #333333;
            text-decoration: none;
        }

        .quick-link:hover { background: #f7f7f7; }

        .panel {
            margin-bottom: 15px;
            padding: 18px;
            border: 1px solid #d2d2d2;
            background: #ffffff;
        }

        .panel h2,
        .panel h3 { margin-top: 0; color: #333333; }

        .message {
            margin-bottom: 15px;
            padding: 10px;
            background: #dff3e8;
            color: #17633f;
        }

        .message.error {
            background: #f8dddd;
            color: #8c3030;
        }

        .user {
            margin-bottom: 8px;
            padding: 10px;
            border-bottom: 1px solid #dddddd;
            overflow: hidden;
        }

        .user-actions { float: right; }

        .user-actions form { display: inline-block; margin-left: 5px; }

        form { margin: 0; }

        label {
            display: block;
            margin: 8px 0 4px;
            font-size: 13px;
            font-weight: bold;
        }

        input,
        select {
            width: 100%;
            padding: 7px;
            border: 1px solid #bbbbbb;
            background: #ffffff;
        }

        .user-actions select { width: 115px; }

        button {
            margin-top: 8px;
            padding: 7px 11px;
            border: 0;
            background: #28795c;
            color: #ffffff;
            cursor: pointer;
        }

        .user-actions button { margin-top: 0; }

        button:hover { background: #1d6248; }

        .danger { background: #b94747; }

        .danger:hover { background: #913737; }

        .add-user { margin-top: 18px; padding-top: 18px; border-top: 1px solid #dddddd; }

        .add-user div,
        .sub-panel {
            display: inline-block;
            width: 48%;
            margin-right: 2%;
            vertical-align: top;
        }

        .add-user div:last-child,
        .sub-panel:nth-child(even) { margin-right: 0; }

        .profile-info { margin-bottom: 12px; line-height: 1.5; }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    <main class="main">
        <?php if ($message !== ''): ?><div class="message"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>
        <?php if ($error !== ''): ?><div class="message error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

        <?php if ($section === 'dashboard'): ?>
            <section class="welcome">
                <div><h1>Admin Dashboard</h1><p>Welcome back, <?php echo $adminName; ?>. Manage JerseyTrack from here.</p></div>
                <div class="welcome-badge">JT</div>
            </section>
            <div class="stats">
                <div class="stat"><span>Registered Users</span><strong><?php echo count($users); ?></strong></div>
                <div class="stat"><span>Admin Account</span><strong>Active</strong></div>
                <div class="stat"><span>Revenue This Month</span><strong>BDT <?php echo number_format((float) $revenue['monthly_revenue'], 0); ?></strong></div>
            </div>
            <div class="quick-links">
                <a class="quick-link" href="admin_dashboard.php?section=users"><strong>Manage Users</strong><span>View, add, change roles, or delete users.</span></a>
                <a class="quick-link" href="admin_dashboard.php?section=settings"><strong>Settings & Account</strong><span>Edit your profile, password, and system settings.</span></a>
                <a class="quick-link" href="admin_dashboard.php?section=settings"><strong>Revenue</strong><span><?php echo (int) $revenue['order_count']; ?> orders recorded. Total: BDT <?php echo number_format((float) $revenue['total_revenue'], 0); ?>.</span></a>
            </div>

        <?php elseif ($section === 'users'): ?>
            <section class="panel">
                <h2>Registered Users</h2>
                <?php if (!$users): ?><p>No users have been registered yet.</p><?php endif; ?>
                <?php foreach ($users as $user): ?>
                    <div class="user">
                        <div><strong><?php echo htmlspecialchars($user['name']); ?></strong><br><small>@<?php echo htmlspecialchars($user['username']); ?> - <?php echo htmlspecialchars($user['role']); ?></small></div>
                        <?php if ((int) $user['id'] !== (int) $_SESSION['user_id']): ?>
                            <div class="user-actions">
                                <form method="POST" action="../Controller/AdminController.php">
                                    <input type="hidden" name="action" value="update_role"><input type="hidden" name="user_id" value="<?php echo (int) $user['id']; ?>">
                                    <select name="role">
                                        <?php foreach (['Admin', 'Manager', 'Salesman', 'Customer'] as $role): ?><option value="<?php echo $role; ?>" <?php echo $user['role'] === $role ? 'selected' : ''; ?>><?php echo $role; ?></option><?php endforeach; ?>
                                    </select><button type="submit">Save Role</button>
                                </form>
                                <form method="POST" action="../Controller/AdminController.php" data-confirm="Delete this user?">
                                    <input type="hidden" name="action" value="delete_user"><input type="hidden" name="user_id" value="<?php echo (int) $user['id']; ?>"><button class="danger" type="submit">Delete</button>
                                </form>
                            </div>
                        <?php else: ?><small>Current Admin</small><?php endif; ?>
                    </div>
                <?php endforeach; ?>

                <form class="add-user" method="POST" action="../Controller/AdminController.php">
                    <div><h3>Add New User</h3><label for="new_name">Full Name</label><input id="new_name" name="name" required><label for="new_username">Username</label><input id="new_username" name="username" required></div>
                    <div><h3>&nbsp;</h3><label for="new_password">Password</label><input id="new_password" type="password" name="password" minlength="5" required><label for="new_role">Role</label><select id="new_role" name="role"><option>Customer</option><option>Salesman</option><option>Manager</option><option>Admin</option></select><input type="hidden" name="action" value="create_user"><button type="submit">Add User</button></div>
                </form>
            </section>

        <?php else: ?>
            <div class="settings-grid">
                <section class="panel sub-panel">
                    <h2>System Settings</h2>
                    <form method="POST" action="../Controller/AdminController.php">
                        <input type="hidden" name="action" value="save_settings"><label for="tax_rate">Tax Rate (%)</label><input id="tax_rate" type="number" name="tax_rate" min="0" max="100" step="0.01" value="<?php echo htmlspecialchars($settings['tax_rate']); ?>"><label for="shipping_zone">Shipping Zone</label><select id="shipping_zone" name="shipping_zone"><option value="Inside Dhaka" <?php echo $settings['shipping_zone'] === 'Inside Dhaka' ? 'selected' : ''; ?>>Inside Dhaka</option><option value="Outside Dhaka" <?php echo $settings['shipping_zone'] === 'Outside Dhaka' ? 'selected' : ''; ?>>Outside Dhaka</option></select><button type="submit">Save Settings</button>
                    </form>
                </section>
                <section class="panel sub-panel">
                    <h2>Edit Profile</h2><div class="profile-info"><strong><?php echo htmlspecialchars($admin['name'] ?? 'Admin'); ?></strong><br>Username: <?php echo htmlspecialchars($admin['username'] ?? 'admin'); ?><br>Role: Admin</div>
                    <form method="POST" action="../Controller/AdminController.php"><input type="hidden" name="action" value="update_profile"><label for="profile_name">Full Name</label><input id="profile_name" name="name" value="<?php echo htmlspecialchars($admin['name'] ?? ''); ?>" required><label for="profile_username">Username</label><input id="profile_username" name="username" value="<?php echo htmlspecialchars($admin['username'] ?? ''); ?>" required><button type="submit">Save Profile</button></form>
                </section>
                <section class="panel sub-panel">
                    <h2>Change Password</h2>
                    <form method="POST" action="../Controller/AdminController.php"><input type="hidden" name="action" value="change_password"><label for="current_password">Current Password</label><input id="current_password" type="password" name="current_password" required><label for="new_account_password">New Password</label><input id="new_account_password" type="password" name="new_password" minlength="5" required><button type="submit">Change Password</button></form>
                </section>
                <section class="panel sub-panel">
                    <h2>Delete Profile</h2><p>This permanently removes your Admin account from the database.</p>
                    <form method="POST" action="../Controller/AdminController.php" data-confirm="Delete your Admin profile permanently?"><input type="hidden" name="action" value="delete_profile"><label for="delete_password">Confirm Password</label><input id="delete_password" type="password" name="password" required><button class="danger" type="submit">Delete Profile</button></form>
                </section>
            </div>
        <?php endif; ?>
    </main>
</body>
<script>
    document.querySelectorAll('form[action*="AdminController.php"]').forEach(function (form) {
        form.addEventListener('submit', async function (event) {
            event.preventDefault();

            if (form.dataset.confirm && !confirm(form.dataset.confirm)) {
                return;
            }

            const button = form.querySelector('button[type="submit"]');
            if (button) button.disabled = true;

            const data = new FormData(form);
            data.append('ajax', '1');

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: data,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const result = await response.json();

                if (!result.success) {
                    alert(result.message);
                    if (button) button.disabled = false;
                    return;
                }

                window.location.href = 'admin_dashboard.php?section=' + (result.section || 'dashboard');
            } catch (error) {
                alert('Request failed. Please try again.');
                if (button) button.disabled = false;
            }
        });
    });
</script>
</html>
