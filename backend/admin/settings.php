<?php
$pageTitle = 'Textos e contatos';
require_once __DIR__ . '/_auth.php';
require_once dirname(__DIR__) . '/includes/functions.php';

$keys = [
    'meta_description' => 'Descrição (meta) do site',
    'hours_weekday' => 'Horário — segunda a sexta (texto do banner e rodapé)',
    'hours_saturday' => 'Horário — sábado',
    'about_text' => 'Texto da seção Sobre',
    'about_image' => 'Imagem Sobre (caminho no Vercel, ex.: img/about-us.jpg)',
    'banner_image' => 'Imagem de fundo do banner (ex.: img/bastao-barbearia.jpg)',
    'whatsapp_main_digits' => 'WhatsApp principal só números (ex.: 5548933802543)',
    'team_vlad_name' => 'Nome — Vladimir',
    'team_vlad_phone_display' => 'Telefone exibido — Vladimir',
    'team_vlad_wa_digits' => 'WhatsApp Vladimir só números',
    'team_junior_name' => 'Nome — Júnior',
    'team_junior_phone_display' => 'Telefone exibido — Júnior',
    'team_junior_wa_digits' => 'WhatsApp Júnior só números',
    'address_intro' => 'Texto introdutório da localização',
    'address_line' => 'Endereço (pode usar &lt;br&gt;)',
    'map_iframe_src' => 'URL do iframe do Google Maps',
    'footer_tagline' => 'Texto da primeira coluna do rodapé',
    'footer_address_1' => 'Rodapé — linha endereço 1',
    'footer_address_2' => 'Rodapé — linha endereço 2',
    'footer_address_3' => 'Rodapé — linha endereço 3',
    'footer_hours_1' => 'Rodapé — horário linha 1',
    'footer_hours_2' => 'Rodapé — horário linha 2',
];

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($keys as $k => $_label) {
        $val = $_POST['s_' . $k] ?? '';
        if (is_string($val)) {
            $stmt = $mysqli->prepare('INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)');
            if ($stmt) {
                $stmt->bind_param('ss', $k, $val);
                $stmt->execute();
                $stmt->close();
            }
        }
    }
    $msg = 'Configurações salvas.';
}

$values = [];
$res = $mysqli->query('SELECT setting_key, setting_value FROM settings');
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $values[$row['setting_key']] = $row['setting_value'];
    }
}

require_once __DIR__ . '/admin_header.php';
?>

<h1>Textos e contatos</h1>

<?php if ($msg !== '') { ?><div class="msg ok"><?php echo h($msg); ?></div><?php } ?>

<form method="post">
    <?php foreach ($keys as $key => $label) {
        $v = $values[$key] ?? '';
        ?>
        <div class="row">
            <label for="s_<?php echo h($key); ?>"><?php echo h($label); ?></label>
            <?php
            $useTextarea = in_array($key, ['meta_description', 'about_text', 'address_intro', 'footer_tagline', 'address_line'], true);
            if ($useTextarea) { ?>
                <textarea id="s_<?php echo h($key); ?>" name="s_<?php echo h($key); ?>"><?php echo h($v); ?></textarea>
            <?php } else { ?>
                <input type="text" id="s_<?php echo h($key); ?>" name="s_<?php echo h($key); ?>" value="<?php echo h($v); ?>">
            <?php } ?>
        </div>
    <?php } ?>
    <button type="submit">Salvar tudo</button>
</form>

<?php require_once __DIR__ . '/admin_footer.php'; ?>
