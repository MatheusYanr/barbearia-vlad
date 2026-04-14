<?php
session_start();
if (!empty($_SESSION['admin_logged'])) {
    header('Location: dashboard.php');
    exit;
}
header('Location: login.php');
exit;
