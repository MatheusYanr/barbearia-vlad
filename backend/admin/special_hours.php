<?php
$pageTitle = 'Horários especiais';
require_once __DIR__ . '/_auth.php';
require_once dirname(__DIR__) . '/includes/functions.php';

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $hours = trim($_POST['hours_text'] ?? '');
    if ($title !== '' && $hours !== '') {
        $stmt = $mysqli->prepare('INSERT INTO special_hours (title, hours_text, sort_order) VALUES (?, ?, 99)');
        if ($stmt) {
            $stmt->bind_param('ss', $title, $hours);
            $stmt->execute();
            $stmt->close();
            $msg = 'Registro adicionado.';
        }
    }
}

if (isset($_GET['del'])) {
    $id = (int) $_GET['del'];
    if ($id > 0) {
        $stmt = $mysqli->prepare('DELETE FROM special_hours WHERE id = ?');
        if ($stmt) {
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->close();
            $msg = 'Registro removido.';
        }
    }
}

$rows = [];
$res = $mysqli->query('SELECT id, title, hours_text FROM special_hours ORDER BY sort_order, id');
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $rows[] = $row;
    }
}

require_once __DIR__ . '/admin_header.php';
?>

<h1>Horários especiais</h1>
<p style="color:#aaa;">Use para feriados, recessos ou avisos. Aparecem no banner junto com os horários padrão.</p>
<?php if ($msg !== '') { ?><div class="msg ok"><?php echo h($msg); ?></div><?php } ?>

<table>
    <thead><tr><th>Título</th><th>Texto</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($rows as $r) { ?>
        <tr>
            <td><?php echo h($r['title']); ?></td>
            <td><?php echo h($r['hours_text']); ?></td>
            <td><a href="?del=<?php echo (int) $r['id']; ?>" onclick="return confirm('Excluir?');">Excluir</a></td>
        </tr>
    <?php } ?>
    </tbody>
</table>

<h2>Adicionar</h2>
<form method="post">
    <div class="row">
        <label for="title">Título (ex.: Feriado de Natal)</label>
        <input id="title" name="title" type="text" required>
    </div>
    <div class="row">
        <label for="hours_text">Texto (ex.: Fechado / horário especial)</label>
        <input id="hours_text" name="hours_text" type="text" required>
    </div>
    <button type="submit">Salvar</button>
</form>

<?php require_once __DIR__ . '/admin_footer.php'; ?>
