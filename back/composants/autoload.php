<?php
require_once __DIR__ .'/loadClasses.php';
require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/JWT.php';
require_once __DIR__ . '/checkAccess.php';
session_start();

checkToken($pdo);
?> 