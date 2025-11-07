<?php
session_start();
session_destroy();
header("Location: secure_login.php");
exit();
