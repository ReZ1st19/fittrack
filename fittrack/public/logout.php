<?php
/**
 * Выход из системы
 */

session_start();
require_once DIR . '/../includes/auth.php';

logoutUser();
header('Location: login.php');
exit;