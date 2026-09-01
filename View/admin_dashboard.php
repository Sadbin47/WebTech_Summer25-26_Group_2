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
            background: #f4f4f4;
            color: #333333;
        }

        .main {
            width: 920px;
            margin: 25px auto;
        }

        .welcome {
            margin-bottom: 20px;
            padding: 20px;
            border-bottom: 5px solid #e5a0aa;
            background: #8b1e2d;
            color: #ffffff;
        }

        .welcome h1 { margin: 0 0 8px; }
        .welcome p { margin: 0; color: #f8dce1; }

        .stats { margin-bottom: 20px; }

        .stat {
            display: inline-block;
            width: 32%;
            margin-right: 1%;
            min-height: 135px;
            padding: 22px;
            border: 1px solid #ead1d5;
            background: #ffffff;
            vertical-align: top;
            text-align: left;
        }

        .stat span { color: #765b60; font-size: 14px; }
        .stat strong { display: block; margin-top: 10px; color: #8b1e2d; font-size: 25px; }
        .stat-detail { margin: 12px 0 0; color: #765b60; font-size: 13px; line-height: 1.4; }
        .user small { color: #765b60; font-size: 13px; }

        .panel {
            margin-bottom: 20px;
            padding: 20px;
            border: 1px solid #ead1d5;
            background: #ffffff;
        }

        .panel h2, .panel h3 { margin-top: 0; color: #8b1e2d; }

        .message {
            margin-bottom: 15px;
            padding: 10px;
            background: #fde8ec;
            color: #8b1e2d;
        }

        .message.error {
            background: #f8d7da;
            color: #842029;
        }

        .user {
            margin-bottom: 10px;
            padding: 10px 0;
            border-bottom: 1px solid #ead1d5;
            overflow: hidden;
        }

        .user-actions {
            float: right;
            width: 350px;
        }

        .user-actions form { display: inline-block; margin-left: 5px; }

        form { margin: 0; }

        label { display: block; margin: 8px 0 4px; font-size: 13px; font-weight: bold; }

        input,
        select {
            width: 100%;
            padding: 7px;
            border: 1px solid #d9aab2;
            background: #ffffff;
        }

        .user-actions select { width: 115px; }

        button {
            margin-top: 8px;
            padding: 8px 14px;
            border: 0;
            background: #b42336;
            color: #ffffff;
            cursor: pointer;
        }

        .user-actions button { margin-top: 0; }
        button:hover { background: #8f1d2b; }

        .danger { background: #7f1d1d; }
        .danger:hover { background: #5f1515; }

        .add-user { margin-top: 18px; padding-top: 18px; border-top: 1px solid #ead1d5; }

        .add-user div,
        .sub-panel {
            display: inline-block;
            width: 49%;
            vertical-align: top;
        }

        .profile-info { margin-bottom: 12px; }
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
            </section>
            <div class="stats">
                <div class="stat"><span>Registered Users</span><strong><?php echo count($users); ?></strong><p class="stat-detail">Total accounts currently registered in the JerseyTrack system.</p></div>
                <div class="stat"><span>Admin Account</span><strong>Active</strong><p class="stat-detail">Your administrator account is active and ready to manage the system.</p></div>
                <div class="stat"><span>Revenue This Month</span><strong>BDT <?php echo number_format((float) $revenue['monthly_revenue'], 0); ?></strong><p class="stat-detail"><?php echo (int) $revenue['order_count']; ?> orders recorded with cancelled orders excluded from revenue.</p></div>
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
                // The hidden `action` field shadows the form.action DOM property.
                // Read the HTML attribute explicitly so the request targets the controller.
                const response = await fetch(form.getAttribute('action'), {
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
