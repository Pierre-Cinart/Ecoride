<?php
session_start();
if (isset($_POST['error'])) {
    $_SESSION['error'] = htmlspecialchars($_POST['error']);
}
http_response_code(200);
exit();
