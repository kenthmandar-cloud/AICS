<?php
session_start();
session_destroy();
header("Location: /aics/auth/login.php");
exit;
?>