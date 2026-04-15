<?php

declare(strict_types=1);

session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

header('Content-Type: text/html; charset=UTF-8');

require_once __DIR__ . '/../php/helpers.php';
$conn = require __DIR__ . '/../php/db.php';

if (!$conn instanceof mysqli) {
    http_response_code(500);
    exit('Erro de conexao com o banco. Verifique config.php.');
}

function redirectPanel(string $type, string $message): void
{
    header('Location: index.php?' . $type . '=' . urlencode($message));
    exit;
}

function parsePriceInput(string $raw): ?float
{
    $value = trim($raw);
    if ($value === '') {
        return null;
    }

    $value = str_replace(['R$', ' '], '', $value);

    if (str_contains($value, ',') && str_contains($value, '.')) {
        $value = str_replace('.', '', $value);
    }

    $value = str_replace(',', '.', $value);

    if (!is_numeric($value)) {
        return null;
    }

    return (float)$value;
}

function saveSetting(mysqli $conn, string $key, string $value): bool
{
    $stmt = $conn->prepare(
        'INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)
         ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
    );

    if (!$stmt) {
        return false;
    }

    $stmt->bind_param('ss', $key, $value);
    $ok = $stmt->execute();
    $stmt->close();

    return $ok;
}

function serviceNameExists(mysqli $conn, string $name, int $ignoreId = 0): bool
{
    $stmt = $conn->prepare('SELECT id FROM services WHERE name = ? AND id <> ? LIMIT 1');
    if (!$stmt) {
        return false;
    }

    $stmt->bind_param('si', $name, $ignoreId);
    $stmt->execute();
    $result = $stmt->get_result();
    $exists = $result instanceof mysqli_result && $result->num_rows > 0;

    if ($result instanceof mysqli_result) {
        $result->free();
    }

    $stmt->close();
    return $exists;
}

function parseRatingInput(mixed $value): int
{
    $rating = (int)$value;

    if ($rating < 1) {
        return 1;
    }

    if ($rating > 5) {
        return 5;
    }

    return $rating;
}

function ensureDirectoryExists(string $dirPath): bool
{
    if (is_dir($dirPath)) {
        return true;
    }

    return mkdir($dirPath, 0775, true);
}

function uploadReviewPhoto(array $file, string $absoluteUploadDir, string $relativeUploadDir): array
{
    if (!isset($file['error']) || (int)$file['error'] === UPLOAD_ERR_NO_FILE) {
        return ['ok' => true, 'path' => null, 'error' => ''];
    }

    if ((int)$file['error'] !== UPLOAD_ERR_OK) {
        return ['ok' => false, 'path' => null, 'error' => 'Erro ao enviar foto da avaliacao.'];
    }

    if (!ensureDirectoryExists($absoluteUploadDir)) {
        return ['ok' => false, 'path' => null, 'error' => 'Nao foi possivel preparar a pasta de uploads.'];
    }

    $tmpName = (string)($file['tmp_name'] ?? '');
    if ($tmpName === '' || !is_uploaded_file($tmpName)) {
        return ['ok' => false, 'path' => null, 'error' => 'Arquivo de imagem invalido.'];
    }

    $maxBytes = 5 * 1024 * 1024;
    if ((int)($file['size'] ?? 0) > $maxBytes) {
        return ['ok' => false, 'path' => null, 'error' => 'A foto deve ter no maximo 5MB.'];
    }

    $allowedByMime = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    $mimeType = '';
    if (function_exists('mime_content_type')) {
        $mimeType = (string)(mime_content_type($tmpName) ?: '');
    } elseif (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo !== false) {
            $mimeType = (string)(finfo_file($finfo, $tmpName) ?: '');
            finfo_close($finfo);
        }
    }

    $extension = $allowedByMime[$mimeType] ?? '';

    if ($extension === '') {
        $rawExtension = strtolower(pathinfo((string)($file['name'] ?? ''), PATHINFO_EXTENSION));
        if ($rawExtension === 'jpeg') {
            $rawExtension = 'jpg';
        }

        if (in_array($rawExtension, ['jpg', 'png', 'webp'], true)) {
            $extension = $rawExtension;
        }
    }

    if ($extension === '') {
        return ['ok' => false, 'path' => null, 'error' => 'Formato de imagem invalido. Use JPG, PNG ou WEBP.'];
    }

    try {
        $random = bin2hex(random_bytes(4));
    } catch (Throwable) {
        $random = (string)mt_rand(1000, 9999);
    }

    $newFileName = 'avaliacao_' . date('Ymd_His') . '_' . $random . '.' . $extension;
    $targetAbsolutePath = $absoluteUploadDir . DIRECTORY_SEPARATOR . $newFileName;

    if (!move_uploaded_file($tmpName, $targetAbsolutePath)) {
        return ['ok' => false, 'path' => null, 'error' => 'Nao foi possivel salvar a foto no servidor.'];
    }

    return ['ok' => true, 'path' => $relativeUploadDir . '/' . $newFileName, 'error' => ''];
}

function reviewPhotoForAdminPreview(string $photoPath): string
{
    if (preg_match('/^https?:\/\//i', $photoPath) === 1) {
        return $photoPath;
    }

    return '../' . ltrim($photoPath, '/');
}

$reviewUploadAbsoluteDir = __DIR__ . '/../img/avaliacoes';
$reviewUploadRelativeDir = 'img/avaliacoes';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $action = (string)($_POST['action'] ?? '');

        if ($action === 'add_service') {
            $name = trim((string)($_POST['service_name'] ?? ''));
            $price = parsePriceInput((string)($_POST['service_price'] ?? ''));
            $featured = isset($_POST['service_featured']) ? 1 : 0;

            if ($name === '' || $price === null) {
                redirectPanel('error', 'Informe nome e preco validos para cadastrar o servico.');
            }

            if (serviceNameExists($conn, $name)) {
                redirectPanel('error', 'Ja existe um servico com esse nome.');
            }

            $nextOrder = 1;
            $orderResult = $conn->query('SELECT COALESCE(MAX(sort_order), 0) + 1 AS next_order FROM services');
            if ($orderResult instanceof mysqli_result) {
                $nextOrder = (int)($orderResult->fetch_assoc()['next_order'] ?? 1);
                $orderResult->free();
            }

            $nextId = 1;
            $idResult = $conn->query('SELECT COALESCE(MAX(id), 0) + 1 AS next_id FROM services');
            if ($idResult instanceof mysqli_result) {
                $nextId = (int)($idResult->fetch_assoc()['next_id'] ?? 1);
                $idResult->free();
            }

            if ($featured === 1) {
                $conn->query('UPDATE services SET is_featured = 0');
            }

            $stmt = $conn->prepare('INSERT INTO services (id, name, price, is_featured, sort_order, is_active) VALUES (?, ?, ?, ?, ?, 1)');
            if (!$stmt) {
                redirectPanel('error', 'Nao foi possivel cadastrar o servico.');
            }

            $stmt->bind_param('isdii', $nextId, $name, $price, $featured, $nextOrder);
            $ok = $stmt->execute();
            $stmt->close();

            if (!$ok) {
                redirectPanel('error', 'Erro ao salvar servico no banco.');
            }

            redirectPanel('ok', 'Servico adicionado com sucesso.');
        }

        if ($action === 'update_service') {
            $serviceId = (int)($_POST['service_id'] ?? 0);
            $name = trim((string)($_POST['service_name'] ?? ''));
            $price = parsePriceInput((string)($_POST['service_price'] ?? ''));
            $featured = isset($_POST['service_featured']) ? 1 : 0;

            if ($serviceId <= 0 || $name === '' || $price === null) {
                redirectPanel('error', 'Preencha corretamente os dados do servico para atualizar.');
            }

            if (serviceNameExists($conn, $name, $serviceId)) {
                redirectPanel('error', 'Ja existe outro servico com esse nome.');
            }

            if ($featured === 1) {
                $stmtReset = $conn->prepare('UPDATE services SET is_featured = 0 WHERE id <> ?');
                if ($stmtReset) {
                    $stmtReset->bind_param('i', $serviceId);
                    $stmtReset->execute();
                    $stmtReset->close();
                }
            }

            $stmt = $conn->prepare('UPDATE services SET name = ?, price = ?, is_featured = ? WHERE id = ?');
            if (!$stmt) {
                redirectPanel('error', 'Nao foi possivel atualizar o servico.');
            }

            $stmt->bind_param('sdii', $name, $price, $featured, $serviceId);
            $ok = $stmt->execute();
            $stmt->close();

            if (!$ok) {
                redirectPanel('error', 'Erro ao atualizar o servico.');
            }

            redirectPanel('ok', 'Servico atualizado com sucesso.');
        }

        if ($action === 'delete_service') {
            $serviceId = (int)($_POST['service_id'] ?? 0);

            if ($serviceId <= 0) {
                redirectPanel('error', 'Servico invalido para exclusao.');
            }

            $stmt = $conn->prepare('DELETE FROM services WHERE id = ?');
            if (!$stmt) {
                redirectPanel('error', 'Nao foi possivel remover o servico.');
            }

            $stmt->bind_param('i', $serviceId);
            $ok = $stmt->execute();
            $stmt->close();

            if (!$ok) {
                redirectPanel('error', 'Erro ao remover o servico.');
            }

            redirectPanel('ok', 'Servico removido com sucesso.');
        }

        if ($action === 'save_settings') {
            $fields = [
                'about_text' => (string)($_POST['about_text'] ?? ''),
                'weekday_hours' => (string)($_POST['weekday_hours'] ?? ''),
                'saturday_hours' => (string)($_POST['saturday_hours'] ?? ''),
                'special_hours' => (string)($_POST['special_hours'] ?? ''),
                'phone_1' => (string)($_POST['phone_1'] ?? ''),
                'phone_2' => (string)($_POST['phone_2'] ?? ''),
            ];

            foreach ($fields as $key => $value) {
                if (!saveSetting($conn, $key, trim($value))) {
                    redirectPanel('error', 'Falha ao salvar as configuracoes gerais.');
                }
            }

            redirectPanel('ok', 'Configuracoes atualizadas com sucesso.');
        }

        redirectPanel('error', 'Acao administrativa invalida.');
    } catch (Throwable $exception) {
        error_log('Admin panel POST error: ' . $exception->getMessage());
        redirectPanel('error', 'Nao foi possivel processar a requisicao. Revise os dados e tente novamente.');
    }
}

$settings = loadSettings($conn);
$services = loadServices($conn);
$reviews = loadReviews($conn);

$aboutText = getSetting($settings, 'about_text', '');
$weekdayHours = getSetting($settings, 'weekday_hours', '');
$saturdayHours = getSetting($settings, 'saturday_hours', '');
$specialHours = getSetting($settings, 'special_hours', '');
$phone1 = getSetting($settings, 'phone_1', '');
$phone2 = getSetting($settings, 'phone_2', '');

$successMessage = trim((string)($_GET['ok'] ?? ''));
$errorMessage = trim((string)($_GET['error'] ?? ''));
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Administrativo - Barbearia Vlad</title>
    <link rel="icon" type="image/svg+xml" href="../img/favicon.svg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;700&family=Outfit:wght@300;400;500;600&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="admin.css">
</head>

<body>
    <main class="admin-wrapper">
        <header class="admin-topbar">
            <h1>Painel Administrativo</h1>
            <div class="topbar-actions">
                <a class="link-site" href="../index.php" target="_blank">Abrir site</a>
                <a class="btn-logout" href="logout.php">Sair</a>
            </div>
        </header>

        <?php if ($successMessage !== ''): ?>
        <div class="notice success"><?= h($successMessage) ?></div>
        <?php endif; ?>

        <?php if ($errorMessage !== ''): ?>
        <div class="notice error"><?= h($errorMessage) ?></div>
        <?php endif; ?>

        <div class="grid">
            <section class="card">
                <h2>Adicionar novo serviço</h2>
                <p>Use este formulário para inserir novos serviços na tabela de preços.</p>

                <form method="post" action="" accept-charset="UTF-8">
                    <input type="hidden" name="action" value="add_service">

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="service_name">Nome do serviço</label>
                            <input type="text" id="service_name" name="service_name" required>
                        </div>

                        <div class="form-group">
                            <label for="service_price">Preço</label>
                            <input type="text" id="service_price" name="service_price" placeholder="Ex: 35,00" required>
                        </div>

                        <div class="form-group">
                            <label class="checkbox-wrap" for="service_featured">
                                <input type="checkbox" id="service_featured" name="service_featured">
                                Marcar como mais pedido
                            </label>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button class="btn-primary" type="submit">Adicionar serviço</button>
                    </div>
                </form>
            </section>

            <section class="card">
                <h2>Serviços e preços atuais</h2>
                <p>Edite os valores no mesmo estilo da tabela de preços mostrada no site.</p>

                <div class="service-list">
                    <?php if (empty($services)): ?>
                    <p>Nenhum serviço cadastrado ainda.</p>
                    <?php endif; ?>

                    <?php foreach ($services as $service): ?>
                    <article class="service-item">
                        <form method="post" class="service-main" accept-charset="UTF-8">
                            <input type="hidden" name="action" value="update_service">
                            <input type="hidden" name="service_id" value="<?= (int)$service['id'] ?>">

                            <div class="form-group">
                                <label>Nome</label>
                                <input type="text" name="service_name" value="<?= h((string)$service['name']) ?>" required>
                            </div>

                            <div class="form-group">
                                <label>Preço</label>
                                <input type="text" name="service_price"
                                    value="<?= h(number_format((float)$service['price'], 2, ',', '.')) ?>" required>
                            </div>

                            <div class="form-group">
                                <label class="checkbox-wrap">
                                    <input type="checkbox" name="service_featured"
                                        <?= (int)$service['is_featured'] === 1 ? 'checked' : '' ?>>
                                    Mais pedido
                                </label>
                            </div>

                            <button class="btn-primary" type="submit">Salvar</button>
                        </form>

                        <div class="service-item-actions">
                            <form method="post" onsubmit="return confirm('Deseja remover este serviço?');" accept-charset="UTF-8">
                                <input type="hidden" name="action" value="delete_service">
                                <input type="hidden" name="service_id" value="<?= (int)$service['id'] ?>">
                                <button class="btn-danger" type="submit">Remover</button>
                            </form>
                        </div>
                    </article>
                    <?php endforeach; ?>
                </div>
            </section>

            <section class="card">
                <h2>Informações gerais do site</h2>
                <p>Atualize o texto sobre, horários e telefones que aparecem na home.</p>

                <form method="post" action="" accept-charset="UTF-8">
                    <input type="hidden" name="action" value="save_settings">

                    <div class="form-group">
                        <label for="about_text">Texto Sobre</label>
                        <textarea id="about_text" name="about_text" required><?= h($aboutText) ?></textarea>
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="weekday_hours">Horário (Segunda a Sexta)</label>
                            <input type="text" id="weekday_hours" name="weekday_hours" value="<?= h($weekdayHours) ?>"
                                required>
                        </div>

                        <div class="form-group">
                            <label for="saturday_hours">Horário (Sábado)</label>
                            <input type="text" id="saturday_hours" name="saturday_hours"
                                value="<?= h($saturdayHours) ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="special_hours">Horário para dias especiais</label>
                            <input type="text" id="special_hours" name="special_hours" value="<?= h($specialHours) ?>">
                        </div>

                        <div class="form-group">
                            <label for="phone_1">Telefone 1</label>
                            <input type="text" id="phone_1" name="phone_1" value="<?= h($phone1) ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="phone_2">Telefone 2</label>
                            <input type="text" id="phone_2" name="phone_2" value="<?= h($phone2) ?>" required>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button class="btn-primary" type="submit">Salvar configurações</button>
                    </div>
                </form>
            </section>
        </div>
    </main>
</body>

</html>
