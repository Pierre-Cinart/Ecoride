<?php
session_unset();
session_destroy();
session_start();

// debug session 
echo '<pre>';
print_r($_SESSION);
echo '</pre>';
?>