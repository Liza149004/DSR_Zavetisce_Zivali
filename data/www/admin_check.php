<?php
    //preprečevanje predpomnilnika
    header("Cache-Control: no-cache, no-store, must-revalidate");
    header("Pragma: no-cache");
    header("Expires: 0");

    session_start();
    //če v seji ni admin_id-ja, ga vrzi ven na prijavo
    if (!isset($_SESSION['admin_id'])) {
        header("Location: admin_login.php");
        exit();
    }
?>