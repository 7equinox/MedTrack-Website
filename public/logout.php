<?php
session_start();
session_unset();
session_destroy();

// Redirect to the main index page after logout
header("Location: index.php");
exit();
?> 