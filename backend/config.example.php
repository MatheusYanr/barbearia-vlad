<?php
/**
 * Copie para config.php e preencha. Não commite config.php com senha em repositório público.
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'nome_do_banco');
define('DB_USER', 'usuario_mysql');
define('DB_PASS', 'senha_mysql');

define('SITE_PUBLIC_URL', 'https://seudominio.com');

/** URL exata do site que abre o index (mesmo domínio) ou * para qualquer origem na API. */
define('CORS_ALLOWED_ORIGIN', 'https://seudominio.com');

define('UPLOAD_DIR', __DIR__ . '/uploads/');
