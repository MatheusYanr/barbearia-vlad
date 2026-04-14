<?php
$pageTitle = 'Serviços e preços';
require_once __DIR__ . '/_auth.php';
require_once dirname(__DIR__) . '/includes/functions.php';

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_category') {
        $title = trim($_POST['title'] ?? '');
        $icon = trim($_POST['icon'] ?? 'cut');
        if ($title !== '') {
            $stmt = $mysqli->prepare('INSERT INTO service_categories (title, icon, sort_order) VALUES (?, ?, 99)');
            if ($stmt) {
                $stmt->bind_param('ss', $title, $icon);
                $stmt->execute();
                $stmt->close();
                $msg = 'Categoria adicionada.';
            }
        }
    }

    if ($action === 'add_item') {
        $cid = (int) ($_POST['category_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $price = trim($_POST['price_display'] ?? '');
        if ($cid > 0 && $name !== '') {
            $stmt = $mysqli->prepare('INSERT INTO service_items (category_id, name, price_display, sort_order) VALUES (?, ?, ?, 99)');
            if ($stmt) {
                $stmt->bind_param('iss', $cid, $name, $price);
                $stmt->execute();
                $stmt->close();
                $msg = 'Serviço adicionado.';
            }
        }
    }
}

if (isset($_GET['del_cat'])) {
    $id = (int) $_GET['del_cat'];
    if ($id > 0) {
        $stmt = $mysqli->prepare('DELETE FROM service_categories WHERE id = ?');
        if ($stmt) {
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->close();
            $msg = 'Categoria removida (e seus itens).';
        }
    }
}

if (isset($_GET['del_item'])) {
    $id = (int) $_GET['del_item'];
    if ($id > 0) {
        $stmt = $mysqli->prepare('DELETE FROM service_items WHERE id = ?');
        if ($stmt) {
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->close();
            $msg = 'Item removido.';
        }
    }
}

$cats = [];
$res = $mysqli->query('SELECT id, title, icon FROM service_categories ORDER BY sort_order ASC, id ASC');
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $cats[] = $row;
    }
}

require_once __DIR__ . '/admin_header.php';
?>

<h1>Serviços e preços</h1>
<?php if ($msg !== '') { ?><div class="msg ok"><?php echo h($msg); ?></div><?php } ?>

<h2>Nova categoria</h2>
<form method="post" class="row">
    <input type="hidden" name="action" value="add_category">
    <label>Nome da categoria</label>
    <input type="text" name="title" required placeholder="Ex.: Cortes">
    <label>Ícone</label>
    <select name="icon">
        <option value="cut">Tesoura / cortes</option>
        <option value="blade">Barba / navalha</option>
        <option value="package">Produtos / caixa</option>
    </select>
    <p><button type="submit">Adicionar categoria</button></p>
</form>

<?php foreach ($cats as $c) {
    $cid = (int) $c['id'];
    $items = [];
    $st = $mysqli->prepare('SELECT id, name, price_display FROM service_items WHERE category_id = ? ORDER BY sort_order, id');
    if ($st) {
        $st->bind_param('i', $cid);
        $st->execute();
        $ir = $st->get_result();
        while ($row = $ir->fetch_assoc()) {
            $items[] = $row;
        }
        $st->close();
    }
    ?>
    <h2><?php echo h($c['title']); ?> <small style="color:#888;">(ícone: <?php echo h($c['icon']); ?>)</small>
        <a href="?del_cat=<?php echo (int) $cid; ?>" onclick="return confirm('Apagar esta categoria e todos os itens?');" style="color:#f66;font-size:0.85rem;">Excluir categoria</a>
    </h2>
    <table>
        <thead><tr><th>Serviço</th><th>Preço (texto)</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($items as $it) { ?>
            <tr>
                <td><?php echo h($it['name']); ?></td>
                <td><?php echo h($it['price_display']); ?></td>
                <td><a href="?del_item=<?php echo (int) $it['id']; ?>" onclick="return confirm('Remover este item?');">Excluir</a></td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
    <h3>Adicionar item nesta categoria</h3>
    <form method="post">
        <input type="hidden" name="action" value="add_item">
        <input type="hidden" name="category_id" value="<?php echo (int) $cid; ?>">
        <div class="row">
            <label>Nome do serviço</label>
            <input type="text" name="name" required>
        </div>
        <div class="row">
            <label>Preço (opcional, texto livre)</label>
            <input type="text" name="price_display" placeholder="Ex.: R$ 40,00 ou Consulte">
        </div>
        <button type="submit">Adicionar</button>
    </form>
    <hr style="border-color:#333;margin:32px 0;">
<?php } ?>

<?php require_once __DIR__ . '/admin_footer.php'; ?>
