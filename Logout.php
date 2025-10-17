<?php
session_start();

// Remove all session variables
session_unset();

// Destroy the session
session_destroy();

// Redirect to homepage or login page
header("Location: http://cs3-dev.ict.ru.ac.za/practicals/4a2/HomePage/index.php");
exit();
?>
