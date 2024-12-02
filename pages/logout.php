<?php
session_start();
if (isset($_SESSION['UserId'])) {
    session_unset(); 
    session_destroy();
}
header("Location: login.php");
exit();
?>
