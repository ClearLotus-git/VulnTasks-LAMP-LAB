# VulnTasks-LAMP-LAB Walkthrough

I wanted to keep learning by building and breaking things in a realistic environment, so I decided to create my own.

The goal of this project is to build a small, local web application using the LAMP stack and intentionally leave parts of it insecure. This makes it possible to test, analyze, and document real-world attacks.. and then fix them to compare both sides of the process.

The focus isn’t on making something polished or production-ready, but on understanding how each part actually works and connects from database setup to web forms and backend logic.

For anyone else interested in hands-on security practice, this kind of setup can be a good way to experiment and learn outside of prebuilt or controlled environments.

---

### Choosing the stack

For this project I wanted something lightweight, local, and easy to reset or break without 
affecting anything else on my system. Thats why I used **XAMPP**, which bundles: 

- **Apache** (web server)  
- **MySQL** (database)  
- **PHP** (scripting language)

It's simple and takes 10 minutes to install: **Download:** [https://www.apachefriends.org/download.html](https://www.apachefriends.org/download.html)

<img width="724" height="531" alt="image" src="https://github.com/user-attachments/assets/52d0942a-3144-439c-9099-f46441060d6a" />

---

### Installing XAMPP

1. Downloaded the Windows version of XAMPP (PHP 8.x).
2. During setup, I selected only **Apache**, **MySQL**, and **phpMyAdmin**.
3. Installed it to `C:\xampp` to avoid Windows permission issues.
4. After installation, I started both **Apache** and **MySQL** from the XAMPP Control Panel.

<img width="680" height="544" alt="image" src="https://github.com/user-attachments/assets/d4e36b13-cc20-4e3d-a46e-4e8a10740ef3" />

After starting both services, I confirmed it worked by visiting:

http://localhost 


which loaded the default XAMPP dashboard.

<img width="1090" height="701" alt="image" src="https://github.com/user-attachments/assets/e22df060-0d86-4979-ab18-db75164be8dc" />

---

### Creating the database

Next, I opened phpMyAdmin at:

http://localhost/phpmyadmin 

and created a new database called **vulntasks**.

<img width="511" height="356" alt="image" src="https://github.com/user-attachments/assets/ccaac7a2-1567-4455-baa1-4dbdbb473977" />

Instead of using the GUI to create tables, I went to the SQL tab (shown below) and entered the following commands manually:

```
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255),
    password VARCHAR(255)
);

INSERT INTO users (username, password)
VALUES
('admin', 'admin123'),
('lotus', 'password'),
('testuser', '1234');
```

This created a simple table for login testing, which will later be used for intentionally vulnerable authentication and SQL injection experiments.

<img width="1198" height="647" alt="image" src="https://github.com/user-attachments/assets/d76ded44-59c1-42d5-9c81-d527e36af8ac" />

This will later be used to test authentication and SQL injection.


### Project folder setup

Created a new project folder inside:

`C:\xampp\htdocs\VulnTasks-LAMP-LAB`

This is the web root where Apache serves local projects.  
Inside that folder I added:

```
includes/
public_html/
sql/
```

Each folder has its own purpose:
- **includes** → configuration and database connection files  
- **public_html** → web pages and scripts that run in the browser  
- **sql** → database setup scripts

<img width="531" height="236" alt="image" src="https://github.com/user-attachments/assets/7735e018-6d4c-491e-a15a-055b7a709fa9" />

---

### Testing PHP and MySQL connectivity

Before adding anything complex, I wrote a small PHP file to check that MySQL was reachable (I was using VSCode).

File created at:

`C:\xampp\htdocs\VulnTasks-LAMP-LAB\public_html\test_db.php`


Linked to:

`C:\xampp\htdocs\VulnTasks-LAMP-LAB\includes\db_connect.php`

Inside `test_db.php`:
```
<?php
// Show all PHP errors for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Try to include database connection
echo "<h3>Testing database connection...</h3>";

$path = "../includes/db_connect.php";
if (file_exists($path)) {
    include($path);
    echo "<p>db_connect.php included successfully.</p>";
} else {
    echo "<p style='color:red;'>❌ Could not find db_connect.php at $path</p>";
    exit;
}

// Test if connection object is valid
if (isset($conn) && $conn->ping()) {
    echo "<p style='color:green;'>Database connection successful!</p>";
} else {
    echo "<p style='color:red;'>Database connection failed: " . $conn->connect_error . "</p>";
}
?>
```

Inside `db_connect.php`:

```
<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "vulntasks";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
```

Running it at:

http://localhost/VulnTasks-LAMP-LAB/public_html/test_db.php

After creating the database correctly, it displayed:

<img width="288" height="121" alt="image" src="https://github.com/user-attachments/assets/b4dd1c68-f5c7-4880-828d-95b6790891e5" />

That confirmed everything in the LAMP stack was communicating correctly:

- PHP connected to MySQL
- Apache served the page locally
- The database was accessible

From here, the next part will be adding an intentionally vulnerable login form — then testing things like SQL injection and improper authentication handling before locking them down.

---

### Building a Insecure Login Page: 

I needed to tie my PHP front-end to my MySQL backend to focus on an SQL injection.

Creating a file called login.php inside of my project folder at:

`C:\xampp\htdocs\VulnTasks-LAMP-LAB\public_html\`

`login.php` basic code:

```
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
```

---

### Testing and Demonstrating SQL Injection

Once that was created it was time to visit the login webpage at:

http://localhost/VulnTasks-LAMP-LAB/public_html/login.php

<img width="503" height="281" alt="image" src="https://github.com/user-attachments/assets/94f5924b-94a9-4be1-84f1-268b4c7707f6" />

After entering the correct credentials {admin:admin123} there was a "Login successful" message:

<img width="509" height="367" alt="image" src="https://github.com/user-attachments/assets/540842b5-863c-46d3-91f9-395c1daaf44a" />

Testing the login with incorrect credentials {test:testing} : 

<img width="457" height="350" alt="image" src="https://github.com/user-attachments/assets/2a6f5fc3-e46f-4639-84df-e5085d44a539" />


I now wanted to test what would happen if I attempted an SQL injection with either of the below and it is successful.

```
' OR 1=1 --
' UNION SELECT null,null,null --
```

<img width="628" height="359" alt="image" src="https://github.com/user-attachments/assets/720e21d1-ed33-4807-b524-fe24b86fd636" />

This confirms that the current login logic directly concatenates user input into the SQL query without validation or parameter binding.

Root cause:

```
$query = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";

```

Because variables are interpolated directly, the database interprets injected code.

---


### Understanding the Logic and Preparing a Secure Login Flow

In order to prevent the SQL query I will be creating a more secure way the user can 
get to a Dashboard screen.

Let's understand the workflow planned:

1. User opens `secure_login.php`
2. User enters credentials -> form submits a POST request
3. PHP:
- connects to the database
- runs a prepared query
- verifies the password hash
4. If it matches -> create a PHP session ($_SESSION)
5. Redirects to `dashboard.php` -> showing a welcome page only if the session exists.

  
Folder structure:

```
    Directory: C:\xampp\htdocs\VulnTasks-LAMP-LAB\public_html


Mode                 LastWriteTime         Length Name
----                 -------------         ------ ----
-a----         11/6/2025  11:38 PM              0 dashboard.php  <-- new
-a----         11/6/2025   5:14 PM           1178 login.php
-a----         11/6/2025  11:30 PM              0 secure_login.php <--- new
-a----         11/6/2025   5:04 PM            711 test_db.php
```

### Modifying the Table Structure

We will keep the old passwords column for comparison later. In phpMyAdmin

Go to users -> Structure -> Add 1 column after `password`

<img width="1132" height="534" alt="image" src="https://github.com/user-attachments/assets/f31f8bcc-d980-484d-a608-b3cfa9fce79b" />

Name it: `password_hash` -> Type: `VARCHAR` Length: `255`

<img width="854" height="395" alt="image" src="https://github.com/user-attachments/assets/e2283ca9-9f83-4025-9759-e841680b5937" />

### Generate Hashed Passwords

Let's generate secure hashes for the users.

To do this, I'd recommend created a PHP snippet:

Create a temporary file called:

public_html/generate_hash.php and add each of the passwords.

```
<?php
echo password_hash('admin123', PASSWORD_DEFAULT);
```

Go to the location in the browser:

http://localhost/VulnTasks-LAMP-LAB/public_html/generate_hash.php and get the hash:

<img width="715" height="159" alt="image" src="https://github.com/user-attachments/assets/befe8a0b-51a6-4d3c-a6ab-edd771d25724" />

Do this for the other two users.

Insert the hashes back in the phpMyAdmin: 

<img width="842" height="306" alt="image" src="https://github.com/user-attachments/assets/6f5e2a19-631d-4ff9-8cb5-928bf1dbcff5" />

In the above screenshot the passwords and hashes are there but we will only need to focus 
on the hashes because they can't be reversed.. only verified. 

### Building and Testing the Secure Login

Now that the database has both plaintext and hashed passwords, we'll create the secure login page
that uses prepared statements and `password_verify`.

Add the coding for `secure_login.php`: 

`secure_login.php`
```
<?php
session_start();
require_once '../includes/db_connect.php';

// Run only if form submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Prepare SQL query safely
    $stmt = $conn->prepare("SELECT id, username, password_hash FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    // If user exists
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Verify hashed password
        if (password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "User not found.";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Secure Login | VulnTasks</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <h2>Secure Login</h2>
  <?php if (!empty($error)): ?>
    <p style="color:red;"><?php echo htmlspecialchars($error); ?></p>
  <?php endif; ?>
  <form method="POST" action="">
    <input type="text" name="username" placeholder="Username" required><br>
    <input type="password" name="password" placeholder="Password" required><br>
    <button type="submit">Login</button>
  </form>
  <p><a href="login.php">Try vulnerable version</a></p>
</body>
</html>
```

And then we will create a simple `dashboard.php` making sure the logged in users have somewhere to go:

`dashboard.php`
```
<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: secure_login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Dashboard</title>
</head>
<body>
  <h2>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
  <p>You’ve successfully logged in using the secure method.</p>
  <a href="logout.php">Logout</a>
</body>
</html>
```

At this time I think it would be good to allow the user to log out. So let's also 
create a `logout.php`:

`logout.php`
```
<?php
session_start();
session_destroy();
header("Location: secure_login.php");
exit();

```

Now that everything looks okay, go to the browser to test:

http://localhost/VulnTasks-LAMP-LAB/public_html/secure_login.php

Logging in with username: `admin` and password: `admin123`

<img width="189" height="178" alt="image" src="https://github.com/user-attachments/assets/1d260f25-b484-43ce-84cc-1cd95ec99fdd" />

<img width="410" height="160" alt="image" src="https://github.com/user-attachments/assets/81174bca-1159-47e5-9140-2576d56cab19" />

Next we are going to log out and try the same injection that we did before to see if it will allow an SQL injection:

<img width="397" height="217" alt="image" src="https://github.com/user-attachments/assets/cb98443a-bc80-4754-bd18-c283e87e4878" />

<img width="345" height="236" alt="image" src="https://github.com/user-attachments/assets/69c6755d-87ba-4cd0-bfb5-e9b583fa5df3" />

### Securing the Login Page: 

This demonstrates how to mitigate and SQL injection using prepared statements and hashed passwords.

Before:

The vulnerable login form concatenated user input directly into SQL queries, allowing attackers to inject payloads to 
bypass authentication.

After:

The new `secure_login.php` shows how:

- Prepared Statements: prevents injected strings from altering the SQL logic.
- Password Hashing: ensures real passwords aren’t stored or exposed.
- Session Management: maintains user state after login.

Code example:

```
$stmt = $conn->prepare("SELECT id, username, password_hash FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
```

Outcome:

It was honestly fun to see how easy it is to break a weak login, and then fix it using a secure version with prepared statements. 
It’s just about thinking like both a user and an attacker at the same time. I hope this demonstration encourages others to make their 
own labs and hack it themselves. 
















