<?php
// logout.php
require_once __DIR__ . '/config/config.php';
unset($_SESSION['einv_user_id']);
unset($_SESSION['einv_username']);
unset($_SESSION['einv_full_name']);
session_destroy();
header("Location: " . BASE_URL . "login.php");
exit;
