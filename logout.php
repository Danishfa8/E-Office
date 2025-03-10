<?php
session_start();


session_unset();
session_destroy();

setcookie('user', "", time() + time() - 3600, "/"); // 86400 = 1 day
setcookie('pass', "", time() + time() - 3600, "/"); // You should not store the password like this in a real application


header("Location: index.php?action=logout");
?>