<?php
require_once __DIR__ . '/../Controller/AuthController.php';
$auth = new AuthController();
$auth->handleLogout();
?>
