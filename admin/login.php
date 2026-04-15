<?php

declare(strict_types=1);

session_start();

if (isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

header('Content-Type: text/html; charset=UTF-8');

$conn = require __DIR__ . '/../php/db.php';
$error = '';
$dbError = '';
$defaultAdminMessage = '';

function ensureDefaultAdmin(mysqli $conn): bool
{
    $username = 'adminvlad';
    $checkStmt = $conn->prepare('SELECT id FROM admins WHERE username = ? LIMIT 1');
    if (!$checkStmt) {
        return false;
    }

    $checkStmt->bind_param('s', $username);
    $checkStmt->execute();
    $existing = $checkStmt->get_result();
    $alreadyExists = $existing instanceof mysqli_result && $existing->num_rows > 0;

    if ($existing instanceof mysqli_result) {
        $existing->free();
    }
    $checkStmt->close();

    if ($alreadyExists) {
        return false;
    }

    $nextId = 1;
    $idResult = $conn->query('SELECT COALESCE(MAX(id), 0) + 1 AS next_id FROM admins');
    if ($idResult instanceof mysqli_result) {
        $nextId = (int) ($idResult->fetch_assoc()['next_id'] ?? 1);
        $idResult->free();
    }

    $passwordHash = password_hash('adminvlad123', PASSWORD_DEFAULT);

    $stmt = $conn->prepare('INSERT INTO admins (id, username, password_hash) VALUES (?, ?, ?)');
    if (!$stmt) {
        return false;
    }

    $stmt->bind_param('iss', $nextId, $username, $passwordHash);
    $ok = $stmt->execute();
    $stmt->close();

    return $ok;
}

if (!$conn instanceof mysqli) {
    $dbError = 'Nao foi possivel conectar no MySQL. Verifique config.php e importe database/schema.sql.';
} else {
    if (ensureDefaultAdmin($conn)) {
        $defaultAdminMessage = 'Primeiro acesso criado: usuario adminvlad e senha adminvlad123.';
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = trim($_POST['username'] ?? '');
        $password = (string) ($_POST['password'] ?? '');

        if ($username === '' || $password === '') {
            $error = 'Informe usuario e senha.';
        } else {
            $stmt = $conn->prepare('SELECT id, username, password_hash FROM admins WHERE username = ? LIMIT 1');
            if (!$stmt) {
                $error = 'Tabela de administradores nao encontrada. Rode o schema.sql no banco.';
            } else {
                $stmt->bind_param('s', $username);
                $stmt->execute();
                $result = $stmt->get_result();
                $admin = $result ? $result->fetch_assoc() : null;
                $stmt->close();

                if ($admin && password_verify($password, (string) $admin['password_hash'])) {
                    session_regenerate_id(true);
                    $_SESSION['admin_id'] = (int) $admin['id'];
                    $_SESSION['admin_user'] = (string) $admin['username'];
                    header('Location: index.php');
                    exit;
                }

                $error = 'Usuario ou senha invalidos.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Administrativo - Barbearia Vlad</title>
    <link rel="icon" type="image/svg+xml" href="../img/favicon.svg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;700&family=Outfit:wght@300;400;500;600&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="admin.css">
</head>

<body>
    <main class="login-shell">
        <section class="login-card">
            <h1>Painel Administrativo</h1>
            <p class="login-help">Acesso para editar servicos, precos, horarios, telefones e texto sobre.</p>

            <?php if ($defaultAdminMessage !== ''): ?>
                <div class="notice success"><?= htmlspecialchars($defaultAdminMessage, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>

            <?php if ($dbError !== ''): ?>
                <div class="notice error"><?= htmlspecialchars($dbError, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>

            <?php if ($error !== ''): ?>
                <div class="notice error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>

            <form method="post" action="">
                <div class="form-group">
                    <label for="username">Usuario</label>
                    <input type="text" id="username" name="username" required>
                </div>

                <div class="form-group">
                    <label for="password">Senha</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <div class="form-actions">
                    <button class="btn-primary" type="submit">Entrar no painel</button>
                    <a class="link-site" href="../index.php">Voltar para o site</a>
                </div>
            </form>
        </section>
    </main>
</body>

</html>