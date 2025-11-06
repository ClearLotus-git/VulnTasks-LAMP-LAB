<?php
// Simple vulnerable login page for demonstration
include '../includes/db_connect.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>VulnTasks Login</title>
</head>
<body style="font-family:Arial; margin:50px;">
  <h2>Login Portal (Vulnerable Demo)</h2>
  <form method="POST" action="">
    <label>Username:</label><br>
    <input type="text" name="username"><br><br>
    <label>Password:</label><br>
    <input type="password" name="password"><br><br>
    <input type="submit" name="submit" value="Login">
  </form>

  <hr>

<?php
if (isset($_POST['submit'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    echo "<p>Checking credentials for: <b>$username</b></p>";

    // intentionally vulnerable (no sanitization)
    $query = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        echo "<p style='color:green;'>Login successful!</p>";
    } else {
        echo "<p style='color:red;'>Invalid username or password.</p>";
    }
}
?>
</body>
</html>
