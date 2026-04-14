<?php
$pageTitle = 'Painel inicial';
require_once __DIR__ . '/_auth.php';
require_once __DIR__ . '/admin_header.php';
?>

<h1>Painel inicial</h1>
<p>Olá, <strong><?php echo h($_SESSION['admin_user'] ?? 'admin'); ?></strong>. Use o menu acima para editar o conteúdo do site.</p>
<ul>
    <li><strong>Textos e contatos</strong> — sobre, horários padrão, endereço, mapa, WhatsApp da equipe.</li>
    <li><strong>Serviços e preços</strong> — categorias, itens e valores exibidos no site e na tabela de preços.</li>
    <li><strong>Horários especiais</strong> — feriados, eventos ou avisos.</li>
    <li><strong>Avaliações</strong> — nomes fictícios, texto e foto.</li>
    <li><strong>Galeria</strong> — envio de fotos (ficam no servidor PHP).</li>
</ul>
<p style="color:#888;font-size:0.9rem;">O site público no Vercel lê os dados pela API <code>api/site.php</code>. Depois de alterar aqui, atualize a página do site.</p>

<?php require_once __DIR__ . '/admin_footer.php'; ?>
