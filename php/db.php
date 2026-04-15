<?php

declare(strict_types=1);

$config = require __DIR__ . '/../config.php';

$conn = @new mysqli(
    $config['db_host'],
    $config['db_user'],
    $config['db_password'],
    $config['db_name'],
    (int) $config['db_port']
);

if ($conn->connect_error) {
    return null;
}

$conn->set_charset('utf8mb4');

return $conn;
