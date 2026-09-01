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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; background: #f4f7f6; color: #24322d; font-family: Arial, sans-serif; }
        .main { width: 94%; max-width: 1100px; margin: auto; padding: 28px 0; }
        .welcome, .panel, .stat { border: 1px solid #dbe5e0; border-radius: 10px; background: #fff; box-shadow: 0 3px 12px rgba(27, 65, 48, .06); }
        .welcome { display: flex; align-items: center; justify-content: space-between; gap: 18px; margin-bottom: 20px; padding: 25px; background: #245a45; color: white; }
        .welcome h1 { margin: 0 0 6px; font-size: 27px; }
        .welcome p { margin: 0; color: #d9eee4; }
        .welcome-badge { font-size: 35px; }
        .stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-bottom: 20px; }
        .stat { padding: 20px; }
        .stat span { color: #687a71; font-size: 13px; }
        .stat strong { display: block; margin-top: 8px; color: #245a45; font-size: 27px; }
        .quick-links { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; }
        .quick-link { display: block; padding: 24px; border-radius: 10px; background: #fff; color: #245a45; text-decoration: none; box-shadow: 0 3px 12px rgba(27, 65, 48, .06); }
        .quick-link:hover { background: #e9f4ef; }
        .quick-link strong { display: block; margin-bottom: 8px; font-size: 17px; }
        .quick-link span { color: #687a71; font-size: 13px; }
        .panel { padding: 24px; }
        .panel h2 { margin: 0 0 18px; color: #245a45; font-size: 20px; }
        .panel h3 { margin: 0 0 10px; font-size: 16px; }
        .message { margin-bottom: 18px; padding: 11px 14px; border-radius: 6px; background: #dff3e8; color: #17633f; }
        .message.error { background: #f8dddd; color: #8c3030; }
        .user { display: flex; align-items: center; justify-content: space-between; gap: 15px; margin-bottom: 10px; padding: 13px; border: 1px solid #dbe5e0; border-radius: 7px; }
        .user small { color: #687a71; }
        .user-actions { display: flex; align-items: center; gap: 7px; }
        .user-actions form { display: flex; align-items: center; gap: 6px; }
        form { margin: 0; }
        label { display: block; margin: 11px 0 5px; font-size: 13px; font-weight: bold; }
        input, select { width: 100%; padding: 9px; border: 1px solid #bdcec5; border-radius: 5px; background: #fff; }
        .user-actions select { width: auto; }
        button { display: inline-block; margin-top: 10px; padding: 9px 13px; border: 0; border-radius: 5px; background: #28795c; color: #fff; cursor: pointer; }
        button:hover { background: #1d6248; }
        .user-actions button { margin-top: 0; }
        .danger { background: #b94747; }
        .danger:hover { background: #913737; }
        .add-user, .settings-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }
        .add-user { margin-top: 22px; padding-top: 20px; border-top: 1px solid #dbe5e0; }
        .sub-panel { padding: 18px; border: 1px solid #dbe5e0; border-radius: 7px; background: #fbfdfc; }
        .profile-info { margin-bottom: 14px; line-height: 1.8; color: #4c6157; }

        @media (max-width: 700px) {
            .welcome { align-items: flex-start; flex-direction: column; }
            .stats, .quick-links, .add-user, .settings-grid { grid-template-columns: 1fr; }
            .user { align-items: flex-start; flex-direction: column; }
            .user-actions { width: 100%; flex-wrap: wrap; }
        }
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
                <a class="quick-link" href="admin_dashboard.php?section=users"><strong>Manage Users</strong><span>View, add, change roles, or delete registered users.</span></a>
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
