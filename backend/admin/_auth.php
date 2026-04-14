<?php
session_start();
if (empty($_SESSION['admin_logged']) || $_SESSION['admin_logged'] !== true) {
    header('Location: login.php');
    exit;
}
require_once dirname(__DIR__) . '/includes/db.php';
