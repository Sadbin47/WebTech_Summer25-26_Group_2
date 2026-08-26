<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$registerError = $_SESSION['register_error'] ?? '';
unset($_SESSION['register_error']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" type="text/css" href="styles.php">
    <title>JerseyTrack: Jersey Inventory & Sales Platform</title>
<script>
function collect_data() {
    let n = document.getElementById("name").value.trim();
    let u = document.getElementById("username").value.trim();
    let p = document.getElementById("password").value.trim();
    let cp = document.getElementById("confirm_password").value.trim();
    let msg = "";
    if(n.length < 5) msg += "Name must be 5 char\n";
    if(u.length < 3) msg += "Username must be 3 char\n";
    if(p.length < 5) msg += "Password must be 5 char\n";
    if(p !== cp) msg += "Passwords must match\n";
    if(msg) { alert(msg); return false; }
    return true;
}
</script>
</head>
<body>
<?php include 'header.php'; ?>
<h2>User Registration</h2>
<?php if ($registerError !== ''): ?>
    <p style="color: white; background: #b94747; padding: 8px 12px;">
        <?php echo htmlspecialchars($registerError); ?>
    </p>
<?php endif; ?>
<form enctype="multipart/form-data" method="POST" action="../Controller/AuthController.php" onsubmit="return collect_data()">
    <input type="hidden" name="action" value="register">
    <table> 
        <tr>
            <td><label for="name">Full Name:</label></td> 
            <td><input type="text" id="name" name="name" placeholder="Enter Full Name" required></td>
        </tr>
        <tr>
            <td><label for="username">Username:</label></td>
            <td><input type="text" id="username" name="username" placeholder="Enter Username" required></td>
        </tr>
        <tr>
            <td><label for="password">Password:</label></td>
            <td><input type="password" id="password" name="password" required></td>
        </tr>
        <tr>
            <td><label for="confirm_password">Confirm Password:</label></td>
            <td><input type="password" id="confirm_password" name="confirm_password" required></td>
        </tr>
        <tr>
            <td><label for="role">Role:</label></td>
            <td>
                <select name="role" id="role" required>
                    <option value="Customer">Customer</option>
                    <option value="Salesman">Salesman</option>
                    <option value="Manager">Manager</option>
                </select>
            </td>
        </tr>
        <tr>
            <td colspan="2"> 
                <input type="submit" id="submit" name="submit" value="Register">
                <input type="reset" id="reset" name="reset">
            </td>
        </tr>
    </table>
</form>
<br>
<a href="login.php">Back to Login</a>
</body>
</html>
