<!DOCTYPE html>
<html lang="en">
<head> 
    <link rel="stylesheet" type="text/css" href="styles.php">
    <title>JerseyTrack: Jersey Inventory & Sales Platform</title>
<script>
function collect_data() {
    let e = document.getElementById("email").value.trim();
    let p = document.getElementById("password").value.trim();
    let msg = "";
    if(e.length < 5) msg += "Email must be 5 char\n";
    if(p.length < 5) msg += "Password must be 5 char\n";
    if(msg) { alert(msg); return false; }
    return true;
}
</script>
</head>
<body>
<h2>System Login</h2>
<form method="POST" action="../Controller/AuthController.php" onsubmit="return collect_data()">
    <input type="hidden" name="action" value="login">
    <table> 
        <tr>
            <td><label for="email">Email:</label></td> 
            <td><input type="email" id="email" name="email" placeholder="Enter Email" required></td>
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