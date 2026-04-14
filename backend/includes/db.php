<?php

require_once dirname(__DIR__) . '/config.php';
require_once __DIR__ . '/functions.php';

$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($mysqli->connect_error) {
    die('Erro ao conectar no MySQL: ' . h($mysqli->connect_error));
}
$mysqli->set_charset('utf8mb4');
