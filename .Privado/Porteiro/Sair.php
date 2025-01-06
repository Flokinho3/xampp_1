<?php
session_start();

$_SESSION = [];
session_destroy();
header("Location: ../../.Publico/Entrada/Login.php");
exit;
?>