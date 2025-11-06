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

`http://localhost/VulnTasks-LAMP-LAB/public_html/test_db.php`

After creating the database correctly, it displayed:

<img width="288" height="121" alt="image" src="https://github.com/user-attachments/assets/b4dd1c68-f5c7-4880-828d-95b6790891e5" />

That confirmed everything in the LAMP stack was communicating correctly:

- PHP connected to MySQL
- Apache served the page locally
- The database was accessible

From here, the next part will be adding an intentionally vulnerable login form — then testing things like SQL injection and improper authentication handling before locking them down.






