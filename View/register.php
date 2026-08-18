<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" type="text/css" href="styles.php">
    <title>JerseyTrack: Jersey Inventory & Sales Platform</title>
<script>
function collect_data() {
    let n = document.getElementById("name").value.trim();
    let e = document.getElementById("email").value.trim();
    let p = document.getElementById("password").value.trim();
    let cp = document.getElementById("confirm_password").value.trim();
    let msg = "";
    if(n.length < 5) msg += "Name must be 5 char\n";
    if(e.length < 5) msg += "Email must be 5 char\n";
    if(p.length < 5) msg += "Password must be 5 char\n";
    if(p !== cp) msg += "Passwords must match\n";
    if(msg) { alert(msg); return false; }
    return true;
}
</script>
</head>
<body>
<h2>User Registration</h2>
<form enctype="multipart/form-data" method="POST" action="../Controller/AuthController.php" onsubmit="return collect_data()">
    <input type="hidden" name="action" value="register">
    <table> 
        <tr>
            <td><label for="name">Full Name:</label></td> 
            <td><input type="text" id="name" name="name" placeholder="Enter Full Name" required></td>
        </tr>
        <tr>
            <td><label for="email">Email:</label></td> 
            <td><input type="email" id="email" name="email" placeholder="Enter Email" required></td>
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
                    <option value="Admin">Admin</option>
                </select>
            </td>
        </tr>
        <tr>
            <td><label for="file">Profile Image:</label></td>
            <td><input type="file" id="file" name="file"></td>
        </tr>
        <tr>
            <td colspan="2">
                <input type="checkbox" id="rememberuser" name="rememberuser" value="1">
                <label for="rememberuser">Remember Me</label>
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