<?php
session_start();
session_unset();
session_destroy();
session_start();

$_SESSION['typeOfUser'] = "employee";
// debug session 
echo '<pre>';
print_r($_SESSION);
echo '</pre>';

header("Location: ./user/home.php");
exit();
?>