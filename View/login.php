<?php
session_start();

if (($_SESSION['role'] ?? '') === 'Admin') {
    header('Location: admin_dashboard.php');
    exit;
}

$loginError = $_SESSION['login_error'] ?? '';
$loginMessage = $_SESSION['login_message'] ?? '';
unset($_SESSION['login_error'], $_SESSION['login_message']);
$rememberedUser = htmlspecialchars($_COOKIE['remember_user'] ?? '');
?>

<!DOCTYPE html>
<html lang="en">
<head> 
    <link rel="stylesheet" type="text/css" href="styles.php">
    <title>JerseyTrack: Jersey Inventory & Sales Platform</title>
<script>
function collect_data() {
    let u = document.getElementById("username").value.trim();
    let p = document.getElementById("password").value.trim();
    let msg = "";
    if(u.length < 3) msg += "Username must be at least 3 characters\n";
    if(p.length < 5) msg += "Password must be 5 char\n";
    if(msg) { alert(msg); return false; }
    return true;
}
</script>
</head>
<body>
<?php include 'header.php'; ?>
<h2>System Login</h2>
<?php if ($loginError !== ''): ?>
    <p style="color: white; background: #b94747; padding: 8px 12px;">
        <?php echo htmlspecialchars($loginError); ?>
    </p>
<?php endif; ?>
<?php if ($loginMessage !== ''): ?>
    <p style="color: white; background: #28795c; padding: 8px 12px;">
        <?php echo htmlspecialchars($loginMessage); ?>
    </p>
<?php endif; ?>
<form method="POST" action="../Controller/AuthController.php" onsubmit="return collect_data()">
    <input type="hidden" name="action" value="login">
    <table> 
        <tr>
            <td><label for="username">Username:</label></td>
            <td>
                <input type="text" id="username" name="username" value="<?php echo $rememberedUser; ?>" required>
            </td>
        </tr>
        <tr>
            <td><label for="password">Password:</label></td>
            <td><input type="password" id="password" name="password" required></td>
        </tr>
        <tr>
            <td colspan="2">
                <input type="checkbox" id="remember" name="remember" value="1">
                <label for="remember">Remember Me</label>
            </td>
        </tr>
        <tr>
            <td colspan="2"> 
                <button type="submit" id="submit" name="submit">Login</button>
                <input type="reset" id="reset" name="reset">
            </td>
        </tr>
    </table>
</form>
<br>
<a href="register.php">Create Account</a>
</body>
</html>
