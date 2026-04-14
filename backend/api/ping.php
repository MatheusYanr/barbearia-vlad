<?php
/**
 * Teste rápido: se abrir no navegador e ver JSON {"php_ok":true}, o PHP desta pasta está rodando.
 * Não usa banco de dados.
 */
header('Content-Type: application/json; charset=utf-8');
echo json_encode(['php_ok' => true, 'time' => date('c')]);
