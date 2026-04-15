<?php

define('DB_HOST', 'localhost');
define('DB_NAME', 'nome_do_banco');
define('DB_USER', 'usuario_mysql');
define('DB_PASS', 'senha_mysql');

define('SITE_PUBLIC_URL', 'https://seudominio.infinityfree.me');

/**
 * Domínio do site no Vercel (com https), para CORS.
 * Ex.: https://barbearia-vlad.vercel.app
 * Para testar local com Live Server, pode usar temporariamente '*' (não use * em produção real).
 */
define('CORS_ALLOWED_ORIGIN', 'https://SEU-PROJETO.vercel.app');

/** Pasta de uploads no servidor (não altere se mantiver a pasta uploads ao lado de config.php) */
define('UPLOAD_DIR', __DIR__ . '/uploads/');
