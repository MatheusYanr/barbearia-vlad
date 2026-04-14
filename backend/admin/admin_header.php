<?php
require_once dirname(__DIR__) . '/includes/functions.php';
if (!isset($pageTitle)) {
    $pageTitle = 'Painel';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo h($pageTitle); ?> | Admin Barbearia Vlad</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: Arial, sans-serif; margin: 0; background: #1a1a1a; color: #eee; line-height: 1.5; }
        a { color: #d4a75c; }
        header { background: #341d08; padding: 12px 20px; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 10px; }
        header nav a { margin-right: 14px; font-weight: bold; text-decoration: none; }
        header nav a:hover { text-decoration: underline; }
        main { max-width: 960px; margin: 0 auto; padding: 24px 16px 60px; }
        h1 { color: #d4a75c; font-size: 1.5rem; }
        table { width: 100%; border-collapse: collapse; margin: 16px 0; background: #111; }
        th, td { border: 1px solid #333; padding: 8px; text-align: left; }
        th { background: #2b1a0f; color: #d4a75c; }
        input[type="text"], input[type="password"], input[type="number"], textarea, select {
            width: 100%; max-width: 520px; padding: 8px; border-radius: 4px; border: 1px solid #555; background: #222; color: #fff;
        }
        textarea { min-height: 100px; max-width: 100%; }
        button, .btn-link {
            display: inline-block; padding: 8px 16px; background: #d4a75c; color: #111; border: none; border-radius: 4px;
            cursor: pointer; font-weight: bold; text-decoration: none;
        }
        button:hover, .btn-link:hover { opacity: 0.9; }
        .msg { padding: 10px 12px; border-radius: 4px; margin: 12px 0; }
        .msg.ok { background: #1e3d1e; border: 1px solid #3a6; }
        .msg.err { background: #3d1e1e; border: 1px solid #a33; }
        .row { margin-bottom: 14px; }
        label { display: block; margin-bottom: 4px; color: #ccc; }
        .thumb { max-width: 80px; max-height: 80px; border-radius: 6px; }
    </style>
</head>
<body>
<header>
    <strong style="color:#daa520;">Barbearia Vlad — Admin</strong>
    <nav>
        <a href="dashboard.php">Início</a>
        <a href="settings.php">Textos e contatos</a>
        <a href="services.php">Serviços e preços</a>
        <a href="special_hours.php">Horários especiais</a>
        <a href="reviews.php">Avaliações</a>
        <a href="gallery.php">Galeria</a>
        <a href="logout.php">Sair</a>
    </nav>
</header>
<main>
