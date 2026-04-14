<?php
session_start();
if (!empty($_SESSION['admin_logged'])) {
    header('Location: dashboard.php');
    exit;
}

require_once dirname(__DIR__) . '/includes/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = trim($_POST['username'] ?? '');
    $pass = $_POST['password'] ?? '';

    if ($user === '' || $pass === '') {
        $error = 'Preencha usuário e senha.';
    } else {
        $stmt = $mysqli->prepare('SELECT id, password_hash FROM admin_users WHERE username = ? LIMIT 1');
        if ($stmt) {
            $stmt->bind_param('s', $user);
            $stmt->execute();
            $stmt->store_result();
            $uid = 0;
            $hash = '';
            if ($stmt->num_rows === 1) {
                $stmt->bind_result($uid, $hash);
                $stmt->fetch();
            }
            $stmt->close();
            if ($uid && password_verify($pass, $hash)) {
                $_SESSION['admin_logged'] = true;
                $_SESSION['admin_user'] = $user;
                $_SESSION['admin_id'] = (int) $uid;
                header('Location: dashboard.php');
                exit;
            }
        }
        $error = 'Usuário ou senha inválidos.';
    }
}

$pageTitle = 'Login';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Admin Barbearia Vlad</title>
    <style>
        body { font-family: Arial, sans-serif; background: #111; color: #eee; display: flex; min-height: 100vh; align-items: center; justify-content: center; margin: 0; }
        .box { background: #1c1c1c; padding: 28px; border-radius: 10px; width: 100%; max-width: 380px; box-shadow: 0 8px 24px rgba(0,0,0,0.5); }
        h1 { color: #d4a75c; font-size: 1.25rem; margin-top: 0; }
        label { display: block; margin: 12px 0 4px; color: #ccc; }
        input { width: 100%; padding: 10px; border-radius: 4px; border: 1px solid #444; background: #222; color: #fff; }
        button { margin-top: 18px; width: 100%; padding: 10px; background: #d4a75c; color: #111; border: none; border-radius: 4px; font-weight: bold; cursor: pointer; }
        .err { color: #f88; margin-top: 12px; font-size: 0.9rem; }
    </style>
</head>
<body>
<div class="box">
    <h1>Painel administrativo</h1>
    <p style="color:#aaa;font-size:0.9rem;">Barbearia Vlad — acesso restrito (demonstração acadêmica).</p>
    <?php if ($error !== '') { ?>
        <p class="err"><?php echo h($error); ?></p>
    <?php } ?>
    <form method="post" action="">
        <label for="username">Usuário</label>
        <input id="username" name="username" type="text" autocomplete="username" required>

        <label for="password">Senha</label>
        <input id="password" name="password" type="password" autocomplete="current-password" required>

        <button type="submit">Entrar</button>
    </form>
</div>
</body>
</html>
