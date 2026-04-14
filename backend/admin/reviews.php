<?php
$pageTitle = 'Avaliações';
require_once __DIR__ . '/_auth.php';
require_once dirname(__DIR__) . '/includes/functions.php';

if (!defined('UPLOAD_DIR')) {
    define('UPLOAD_DIR', dirname(__DIR__) . '/uploads/');
}

$msg = '';
$msgIsError = false;

function review_ext_from_image($tmpPath)
{
    $info = @getimagesize($tmpPath);
    if ($info === false) {
        return '';
    }
    $mime = $info['mime'];
    if ($mime === 'image/jpeg') {
        return 'jpg';
    }
    if ($mime === 'image/png') {
        return 'png';
    }
    if ($mime === 'image/gif') {
        return 'gif';
    }
    if ($mime === 'image/webp') {
        return 'webp';
    }
    return '';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $author = trim($_POST['author_name'] ?? '');
    $quote = trim($_POST['quote'] ?? '');
    $stars = (int) ($_POST['stars'] ?? 5);
    if ($stars < 1) {
        $stars = 1;
    }
    if ($stars > 5) {
        $stars = 5;
    }
    $photo_path = trim($_POST['photo_path'] ?? 'img/cliente-placeholder.svg');
    $err = '';

    if ($author === '' || $quote === '') {
        $err = 'Preencha nome e depoimento.';
    } elseif (!empty($_FILES['photo']['tmp_name']) && is_uploaded_file($_FILES['photo']['tmp_name'])) {
        $ext = review_ext_from_image($_FILES['photo']['tmp_name']);
        if ($ext === '') {
            $err = 'Foto: use JPG, PNG, GIF ou WEBP.';
        } else {
            if (!is_dir(UPLOAD_DIR)) {
                mkdir(UPLOAD_DIR, 0755, true);
            }
            $fname = 'review_' . uniqid('', true) . '.' . $ext;
            $dest = UPLOAD_DIR . $fname;
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $dest)) {
                $photo_path = 'uploads/' . $fname;
            } else {
                $err = 'Não foi possível salvar a foto.';
            }
        }
    }

    if ($err === '') {
        $stmt = $mysqli->prepare('INSERT INTO reviews (author_name, quote, stars, photo_path, sort_order) VALUES (?, ?, ?, ?, 99)');
        if ($stmt) {
            $stmt->bind_param('ssis', $author, $quote, $stars, $photo_path);
            $stmt->execute();
            $stmt->close();
            $msg = 'Avaliação adicionada.';
            $msgIsError = false;
        }
    } else {
        $msg = $err;
        $msgIsError = true;
    }
}

if (isset($_GET['del'])) {
    $id = (int) $_GET['del'];
    if ($id > 0) {
        $stmt = $mysqli->prepare('SELECT photo_path FROM reviews WHERE id = ?');
        $path = '';
        if ($stmt) {
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $res = $stmt->get_result();
            $row = $res->fetch_assoc();
            if ($row) {
                $path = $row['photo_path'];
            }
            $stmt->close();
        }
        if (strpos($path, 'uploads/') === 0) {
            $full = dirname(__DIR__) . '/' . $path;
            if (is_file($full)) {
                @unlink($full);
            }
        }
        $stmt = $mysqli->prepare('DELETE FROM reviews WHERE id = ?');
        if ($stmt) {
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->close();
            $msg = 'Avaliação removida.';
        }
    }
}

$rows = [];
$res = $mysqli->query('SELECT id, author_name, quote, stars, photo_path FROM reviews ORDER BY sort_order, id');
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $rows[] = $row;
    }
}

require_once __DIR__ . '/admin_header.php';
?>

<h1>Avaliações</h1>
<p style="color:#aaa;">Use nomes fictícios. Se não enviar foto, use o caminho padrão (placeholder no Vercel) ou envie imagem JPG/PNG.</p>
<?php if ($msg !== '') { ?><div class="msg <?php echo $msgIsError ? 'err' : 'ok'; ?>"><?php echo h($msg); ?></div><?php } ?>

<table>
    <thead><tr><th>Foto</th><th>Nome</th><th>Texto</th><th>Estrelas</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($rows as $r) {
        $src = $r['photo_path'];
        if (strpos($src, 'uploads/') === 0) {
            $src = rtrim(SITE_PUBLIC_URL, '/') . '/' . $src;
        }
        ?>
        <tr>
            <td><?php if ($src !== '') { ?><img class="thumb" src="<?php echo h($src); ?>" alt=""><?php } ?></td>
            <td><?php echo h($r['author_name']); ?></td>
            <td><?php echo h($r['quote']); ?></td>
            <td><?php echo (int) $r['stars']; ?></td>
            <td><a href="?del=<?php echo (int) $r['id']; ?>" onclick="return confirm('Excluir?');">Excluir</a></td>
        </tr>
    <?php } ?>
    </tbody>
</table>

<h2>Adicionar avaliação</h2>
<form method="post" enctype="multipart/form-data">
    <div class="row">
        <label for="author_name">Nome fictício</label>
        <input id="author_name" name="author_name" type="text" required>
    </div>
    <div class="row">
        <label for="quote">Depoimento</label>
        <textarea id="quote" name="quote" required></textarea>
    </div>
    <div class="row">
        <label for="stars">Estrelas (1 a 5)</label>
        <input id="stars" name="stars" type="number" min="1" max="5" value="5">
    </div>
    <div class="row">
        <label for="photo">Foto do “cliente” (opcional)</label>
        <input id="photo" name="photo" type="file" accept="image/jpeg,image/png,image/gif,image/webp">
    </div>
    <div class="row">
        <label for="photo_path">Ou caminho/URL da imagem (se não enviar arquivo)</label>
        <input id="photo_path" name="photo_path" type="text" value="img/cliente-placeholder.svg" placeholder="img/cliente-placeholder.svg">
    </div>
    <button type="submit">Salvar</button>
</form>

<?php require_once __DIR__ . '/admin_footer.php'; ?>
