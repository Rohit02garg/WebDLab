<?php
// Lab 10 Section 6.3 & 7.6: End the Session
session_start();

// session_unset removes the current session variables
session_unset();

// session_destroy ends the server-side session
session_destroy();

// Redirect user back to student login page
header("Location: login.php");
exit;
?>
