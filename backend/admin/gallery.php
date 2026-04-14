<?php
$pageTitle = 'Galeria';
require_once __DIR__ . '/_auth.php';
require_once dirname(__DIR__) . '/includes/functions.php';

if (!defined('UPLOAD_DIR')) {
    define('UPLOAD_DIR', dirname(__DIR__) . '/uploads/');
}

$msg = '';

function gallery_safe_ext_from_mime($mime)
{
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
    $caption = trim($_POST['caption'] ?? '');
    if (empty($_FILES['image']['tmp_name']) || !is_uploaded_file($_FILES['image']['tmp_name'])) {
        $msg = 'Escolha uma imagem.';
    } else {
        $info = @getimagesize($_FILES['image']['tmp_name']);
        if ($info === false) {
            $msg = 'Arquivo de imagem inválido.';
        } else {
            $mime = $info['mime'];
            $ext = gallery_safe_ext_from_mime($mime);
            if ($ext === '') {
                $msg = 'Use JPG, PNG, GIF ou WEBP.';
            } else {
                if (!is_dir(UPLOAD_DIR)) {
                    mkdir(UPLOAD_DIR, 0755, true);
                }
                $fname = 'gallery_' . uniqid('', true) . '.' . $ext;
                $dest = UPLOAD_DIR . $fname;
                if (move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
                    $stmt = $mysqli->prepare('INSERT INTO gallery (filename, caption, sort_order) VALUES (?, ?, 99)');
                    if ($stmt) {
                        $stmt->bind_param('ss', $fname, $caption);
                        $stmt->execute();
                        $stmt->close();
                        $msg = 'Imagem enviada.';
                    }
                } else {
                    $msg = 'Falha ao mover o arquivo.';
                }
            }
        }
    }
}

if (isset($_GET['del'])) {
    $id = (int) $_GET['del'];
    if ($id > 0) {
        $stmt = $mysqli->prepare('SELECT filename FROM gallery WHERE id = ?');
        $fn = '';
        if ($stmt) {
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows === 1) {
                $stmt->bind_result($filename);
                $stmt->fetch();
                $fn = $filename;
            }
            $stmt->close();
        }
        if ($fn !== '') {
            $full = UPLOAD_DIR . $fn;
            if (is_file($full)) {
                @unlink($full);
            }
        }
        $stmt = $mysqli->prepare('DELETE FROM gallery WHERE id = ?');
        if ($stmt) {
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->close();
            $msg = 'Imagem removida.';
        }
    }
}

$rows = [];
$res = $mysqli->query('SELECT id, filename, caption FROM gallery ORDER BY sort_order, id');
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $rows[] = $row;
    }
}

require_once __DIR__ . '/admin_header.php';
?>

<h1>Galeria de fotos</h1>
<p style="color:#aaa;">As fotos ficam na pasta <code>uploads/</code> do servidor PHP. O site no Vercel mostra usando a URL pública.</p>
<?php if ($msg !== '') { ?><div class="msg ok"><?php echo h($msg); ?></div><?php } ?>

<table>
    <thead><tr><th>Miniatura</th><th>Arquivo</th><th>Legenda</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($rows as $r) {
        $url = rtrim(SITE_PUBLIC_URL, '/') . '/uploads/' . rawurlencode($r['filename']);
        ?>
        <tr>
            <td><img class="thumb" src="<?php echo h($url); ?>" alt=""></td>
            <td><?php echo h($r['filename']); ?></td>
            <td><?php echo h($r['caption']); ?></td>
            <td><a href="?del=<?php echo (int) $r['id']; ?>" onclick="return confirm('Excluir esta foto?');">Excluir</a></td>
        </tr>
    <?php } ?>
    </tbody>
</table>

<h2>Enviar nova foto</h2>
<form method="post" enctype="multipart/form-data">
    <div class="row">
        <label for="image">Imagem</label>
        <input id="image" name="image" type="file" accept="image/jpeg,image/png,image/gif,image/webp" required>
    </div>
    <div class="row">
        <label for="caption">Legenda (opcional)</label>
        <input id="caption" name="caption" type="text" placeholder="Ex.: Corte degradê">
    </div>
    <button type="submit">Enviar</button>
</form>

<?php require_once __DIR__ . '/admin_footer.php'; ?>
