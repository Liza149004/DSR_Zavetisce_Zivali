<?php
session_start();
session_unset();
session_destroy();

// Po uničenju seje še brskalniku ukažemo, naj pozabi predpomnilnik
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

$kam = isset($_GET['redirect']) ? $_GET['redirect'] : 'admin_login.php';
header("Location: " . $kam);
exit();
?>