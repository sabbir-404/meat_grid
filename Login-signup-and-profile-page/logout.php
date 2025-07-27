<?php
session_start();   // Start the session

// Destroy all session variables
session_unset();   // remove all session variables
session_destroy(); // destroy the session

// Redirect to login page
header("Location: Login page.html");
exit();
?>
