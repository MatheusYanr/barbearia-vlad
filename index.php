<?php

declare(strict_types=1);

require_once __DIR__ . '/php/helpers.php';
$conn = require __DIR__ . '/php/db.php';

$defaultSettings = [
    'about_text' => 'Nosso objetivo é proporcionar uma experiência única, combinando técnicas tradicionais com tendências atuais, sempre com conforto e qualidade.',
    'weekday_hours' => 'Segunda a Sexta - 9h às 12h e das 13h30 às 19h30',
    'saturday_hours' => 'Sábados - 9h às 12h e das 13h30 às 17h',
    'special_hours' => 'Feriados e dias especiais: consulte no WhatsApp.',
    'phone_1' => '(48) 98494-0065',
    'phone_2' => '(48) 93380-2543',
];

$services = [
    ['id' => 1, 'name' => 'Corte de Cabelo', 'price' => 35, 'is_featured' => 0],
    ['id' => 2, 'name' => 'Barba Simples', 'price' => 25, 'is_featured' => 0],
    ['id' => 3, 'name' => 'Combo (Corte + Barba)', 'price' => 55, 'is_featured' => 1],
    ['id' => 4, 'name' => 'Sobrancelha', 'price' => 10, 'is_featured' => 0],
];

$reviews = [
    [
        'id' => 1,
        'client_name' => 'Cliente',
        'quote' => 'Ótimo atendimento e serviço de qualidade!',
        'rating' => 5,
        'photo_path' => 'img/pessoa1.jpg',
    ],
    [
        'id' => 2,
        'client_name' => 'Cliente',
        'quote' => 'Ambiente confortável e profissionais excelentes.',
        'rating' => 5,
        'photo_path' => 'img/pessoa2.jpg',
    ],
    [
        'id' => 3,
        'client_name' => 'Cliente',
        'quote' => 'Melhor barbearia da região, recomendo!',
        'rating' => 5,
        'photo_path' => 'img/pessoa2.jpg',
    ],
];

$settings = $defaultSettings;

if ($conn instanceof mysqli) {
    $dbSettings = loadSettings($conn);
    if (!empty($dbSettings)) {
        $settings = array_merge($settings, $dbSettings);
    }

    $dbServices = loadServices($conn);
    if (!empty($dbServices)) {
        $services = $dbServices;
    }

    $dbReviews = loadReviews($conn);
    if (!empty($dbReviews)) {
        $reviews = $dbReviews;
    }
}

$aboutText = getSetting($settings, 'about_text', $defaultSettings['about_text']);
$weekdayHours = getSetting($settings, 'weekday_hours', $defaultSettings['weekday_hours']);
$saturdayHours = getSetting($settings, 'saturday_hours', $defaultSettings['saturday_hours']);
$specialHours = getSetting($settings, 'special_hours', $defaultSettings['special_hours']);
$phone1 = getSetting($settings, 'phone_1', $defaultSettings['phone_1']);
$phone2 = getSetting($settings, 'phone_2', $defaultSettings['phone_2']);

$whatsapp1 = whatsappLink($phone1);
$whatsapp2 = whatsappLink($phone2);
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description"
        content="Barbearia Vlad - Cortes sociais, degradês, barba e estética masculina. Proporcionamos uma experiência única com conforto e excelência.">
    <meta name="keywords" content="barbearia, corte de cabelo, barba, Vlad, salão masculino">
    <title>Barbearia Vlad - Cortes, Barba e Estilo</title>

    <link rel="icon" type="image/svg+xml" href="img/favicon.svg">
    <link rel="shortcut icon" href="img/favicon.svg" type="image/svg+xml">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;700&family=Outfit:wght@300;400;500;600&display=swap"
        rel="stylesheet">

    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css?v=2">
</head>

<body>

    <header class="topo" role="banner">
        <a href="#call-to-action" id="logo" class="logo" aria-label="Ir para o início da página">BARBEARIA VLAD</a>

        <button id="btnMobile" aria-label="Abrir menu" aria-haspopup="true" aria-controls="menu" aria-expanded="false">
            <span class="hamburger"></span>
        </button>

        <nav id="menu" class="menu" aria-label="Navegação Principal">
            <a href="#call-to-action" class="menu-link">Início</a>
            <a href="#sobre" class="menu-link">Sobre</a>
            <a href="#precos" class="menu-link">Preços</a>
            <a href="#equipe" class="menu-link">Equipe</a>
            <a href="#localizacao" class="menu-link">Localização</a>
            <a href="#avaliacoes" class="menu-link">Avaliações</a>
        </nav>
    </header>

    <main role="main">

        <section id="call-to-action" class="banner" aria-labelledby="banner-titulo">
            <img id="cta-img" src="img/bastao-barbearia.jpg"
                alt="Interior escuro focado no clássico de uma barbearia moderna" loading="lazy">

            <div class="banner-left" data-aos="fade-right">
                <h1 id="banner-titulo">BARBEARIA</h1>
                <h2>VLAD</h2>
                <div class="linha" aria-hidden="true"></div>
                <a href="<?= h($whatsapp2) ?>" class="btn" aria-label="Agendar via WhatsApp">WhatsApp</a>
            </div>

            <div class="banner-center" data-aos="fade-left">
                <h3>Horários</h3>
                <p><?= h($weekdayHours) ?></p>
                <p><?= h($saturdayHours) ?></p>
                <p class="horario-especial"><?= h($specialHours) ?></p>
            </div>
        </section>

        <section id="sobre" class="sobre" aria-labelledby="sobre-titulo">
            <article class="sobre-container" data-aos="fade-up">
                <figure class="sobre-img">
                    <div class="img-placeholder">
                        <img src="img/about-us.jpg"
                            alt="Equipe de barbeiros cuidando rigorosamente do cabelo dos clientes" loading="lazy">
                    </div>
                </figure>
                <div class="sobre-texto">
                    <h2 id="sobre-titulo">Sobre Nós</h2>
                    <p><?= nl2br(h($aboutText)) ?></p>
                </div>
            </article>
        </section>

        <section id="precos" class="precos" aria-labelledby="precos-titulo">
            <div class="precos-wrapper" data-aos="fade-up">
                <h2 id="precos-titulo">Serviços e Preços</h2>

                <div class="precos-lista">
                    <?php foreach ($services as $service): ?>
                        <article class="preco-item<?= (int) $service['is_featured'] === 1 ? ' destaque' : '' ?>">
                            <?php if ((int) $service['is_featured'] === 1): ?>
                                <span class="preco-badge">Mais pedido</span>
                            <?php endif; ?>

                            <h3 class="preco-nome"><?= h((string) $service['name']) ?></h3>
                            <span class="preco-linha" aria-hidden="true"></span>
                            <strong class="preco-valor"><?= h(formatPriceBR((float) $service['price'])) ?></strong>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <section id="equipe" class="equipe" aria-labelledby="equipe-titulo">
            <header class="equipe-topo" data-aos="fade-up">
                <h2 id="equipe-titulo">Nossa Equipe</h2>
                <hr class="linha" aria-hidden="true">
            </header>
            <div class="equipe-container">
                <article class="membro" data-aos="flip-left" data-aos-delay="100">
                    <h3>Vladimir</h3>
                    <p>
                        <a href="<?= h($whatsapp1) ?>" target="_blank" class="wpp-contato"
                            aria-label="Enviar mensagem para o Barbeiro Vladimir no WhatsApp">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/6/6b/WhatsApp.svg"
                                alt="Ícone oficial do WhatsApp" class="wpp-icon" loading="lazy">
                            <?= h($phone1) ?>
                        </a>
                    </p>
                </article>
                <article class="membro" data-aos="flip-right" data-aos-delay="200">
                    <h3>Júnior</h3>
                    <p>
                        <a href="<?= h($whatsapp2) ?>" target="_blank" class="wpp-contato"
                            aria-label="Enviar mensagem para o Barbeiro Júnior no WhatsApp">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/6/6b/WhatsApp.svg"
                                alt="Ícone oficial do WhatsApp" class="wpp-icon" loading="lazy">
                            <?= h($phone2) ?>
                        </a>
                    </p>
                </article>
            </div>
        </section>

        <section id="localizacao" class="localizacao" aria-labelledby="localizacao-titulo">
            <div class="localizacao-container">
                <div class="localizacao-texto" data-aos="fade-right">
                    <h2 id="localizacao-titulo">Onde Atendemos</h2>
                    <p>Venha nos visitar! Estamos localizados em um ponto estratégico e de fácil acesso para melhor lhe
                        atender.</p>
                    <p><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="svg-icon-inline" aria-hidden="true">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                            <circle cx="12" cy="10" r="3"></circle>
                        </svg> <strong>Endereço:</strong> Rua Otto Júlio Malina, 767 - Bairro Ipiranga<br>São José / SC
                    </p>
                </div>
                <div class="localizacao-mapa" data-aos="fade-left">
                    <iframe title="Mapa de localização interativo da barbearia pelo Google Maps"
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3536.967103636885!2d-48.62273730000001!3d-27.5635314!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x952749ab569c7c5f%3A0x2caa2da2126fadde!2sBarbearia%20Vlad!5e0!3m2!1spt-BR!2sbr!4v1776116414677!5m2!1spt-BR!2sbr"
                        width="100%" height="300"
                        style="border:0; border-radius:15px; box-shadow: 0 6px 18px rgba(0,0,0,0.45);"
                        allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>
            </div>
        </section>

        <section id="avaliacoes" class="avaliacoes" aria-labelledby="avaliacoes-titulo" data-aos="fade-up">
            <h2 id="avaliacoes-titulo">Avaliações</h2>

            <?php
            $gRating = getSetting($settings, 'google_rating', '4.8');
            $gTotal = getSetting($settings, 'google_total_ratings', '59');
            ?>
            <div class="google-status" style="text-align: center; margin-bottom: 2rem; color: #f2a900; font-family: 'Playfair Display', serif;">
                <p style="font-size: 1.5rem; margin: 0; padding-bottom: 0.3rem;">
                    <strong>Google: <?= h($gRating) ?> ⭐</strong>
                </p>
                <p style="font-size: 1rem; margin: 0; color: #d4c8b8; font-family: 'Raleway', sans-serif;">
                    Baseado em <?= h($gTotal) ?> avaliações reais
                </p>
            </div>

            <div class="film-carousel">
                <div class="film-track" id="filmTrack">
                    <?php 
                    $static_reviews = [
                        ['client_name' => 'Carlos Silva', 'rating' => 5, 'quote' => 'Excelente atendimento! A barbearia tem um clima sensacional e o corte ficou impecável.'],
                        ['client_name' => 'Rafael Souza', 'rating' => 5, 'quote' => 'Ambiente confortável e profissionais excelentes. O melhor degradê da região, sem dúvidas.'],
                        ['client_name' => 'João Mendes', 'rating' => 5, 'quote' => 'Ótimo atendimento e serviço de qualidade! Lugar muito bem decorado e limpo.'],
                        ['client_name' => 'Lucas Peixoto', 'rating' => 5, 'quote' => 'Profissionais caprichosos, cerveja gelada e preço justo. Vale cada centavo.'],
                        ['client_name' => 'Matheus Costa', 'rating' => 5, 'quote' => 'Muito bom, recomendo demais! Barba feita na toalha quente é uma experiência à parte.']
                    ];
                    foreach ($static_reviews as $review): 
                    ?>
                        <?php
                        $reviewRating = (int) ($review['rating'] ?? 5);
                        $reviewClientName = trim((string) ($review['client_name'] ?? 'Cliente'));
                        $reviewQuote = trim((string) ($review['quote'] ?? ''));
                        ?>
                        <figure class="avaliacao">
                            <div class="estrelas" aria-label="Avaliação de <?= $reviewRating ?> estrelas">
                                <?= str_repeat('&#9733;', $reviewRating) ?>
                            </div>
                            <blockquote>"<?= h($reviewQuote) ?>"</blockquote>
                            <figcaption class="cliente-info">
                                <span>- <?= h($reviewClientName) ?></span>
                            </figcaption>
                        </figure>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="google-review-actions" style="display: flex; gap: 1rem; justify-content: center; margin-top: 2rem; flex-wrap: wrap;">
                <a href="https://www.google.com/search?sca_esv=55e9f3c856495c1e&sxsrf=ANbL-n6E5Snapkyp90sP6ftW-z4n5mxkBg:1776279545357&si=AL3DRZEsmMGCryMMFSHJ3StBhOdZ2-6yYkXd_doETEE1OR-qOUZUn3xWmF60iy-NIAzl8mmrs919Dnhtc2-o8hYHfKuk5fQAteuYZGJFM16dNlH153u__Mtt2A89oKOnFXQSCNCyrMuzk9ZPI5u_TlCieco7HBYJbg%3D%3D&q=Barbearia+Vlad+Coment%C3%A1rios&sa=X&ved=2ahUKEwi3lMr6xPCTAxUeIrkGHWEWOt0Q0bkNegQIIRAF&biw=1920&bih=836&dpr=1#lrd=0x952749ab569c7c5f:0x2caa2da2126fadde,3,,,," target="_blank" rel="noopener noreferrer" style="background-color: #f2a900; color: #1a1a1a; padding: 0.8rem 1.5rem; text-decoration: none; border-radius: 5px; font-weight: bold; transition: all 0.3s ease; box-shadow: 0 4px 6px rgba(0,0,0,0.3);" onmouseover="this.style.backgroundColor='#d19200'" onmouseout="this.style.backgroundColor='#f2a900'">
                    Adicionar avaliação
                </a>
                <a href="https://www.google.com/search?sca_esv=55e9f3c856495c1e&sxsrf=ANbL-n6E5Snapkyp90sP6ftW-z4n5mxkBg:1776279545357&si=AL3DRZEsmMGCryMMFSHJ3StBhOdZ2-6yYkXd_doETEE1OR-qOUZUn3xWmF60iy-NIAzl8mmrs919Dnhtc2-o8hYHfKuk5fQAteuYZGJFM16dNlH153u__Mtt2A89oKOnFXQSCNCyrMuzk9ZPI5u_TlCieco7HBYJbg%3D%3D&q=Barbearia+Vlad+Coment%C3%A1rios&sa=X&ved=2ahUKEwi3lMr6xPCTAxUeIrkGHWEWOt0Q0bkNegQIIRAF&biw=1920&bih=836&dpr=1#lrd=" target="_blank" rel="noopener noreferrer" style="background-color: transparent; color: #f2a900; border: 2px solid #f2a900; padding: 0.8rem 1.5rem; text-decoration: none; border-radius: 5px; font-weight: bold; transition: all 0.3s ease;" onmouseover="this.style.backgroundColor='rgba(242, 169, 0, 0.1)'" onmouseout="this.style.backgroundColor='transparent'">
                    Visualizar mais avaliações
                </a>
            </div>
        </section>

    </main>

    <footer class="rodape" role="contentinfo" data-aos="fade-up">
        <div class="rodape-container">
            <div class="rodape-coluna logo-coluna">
                <h2 class="rodape-logo">BARBEARIA VLAD</h2>
                <p>A experiência de barbearia mais tradicional da sua região. Cortes modernos e um ambiente pensado para
                    o
                    homem contemporâneo.</p>
            </div>
            <div class="rodape-coluna">
                <h3>Visite-nos</h3>
                <p>Rua Otto Júlio Malina, 767</p>
                <p>Bairro Ipiranga</p>
                <p>São José / SC</p>
            </div>
            <div class="rodape-coluna">
                <h3>Contato</h3>
                <p><?= h($phone1) ?></p>
                <p><?= h($phone2) ?></p>
            </div>
            <div class="rodape-coluna">
                <h3>Horários</h3>
                <p><?= h($weekdayHours) ?></p>
                <p><?= h($saturdayHours) ?></p>
                <p><?= h($specialHours) ?></p>
            </div>
        </div>
        <div class="rodape-bottom">
            <p>&copy; 2026 Barbearia Vlad - Todos os direitos reservados</p>
        </div>
    </footer>

    <script>
        const btnMobile = document.getElementById('btnMobile');
        const menu = document.getElementById('menu');
        const menuLinks = document.querySelectorAll('.menu-link');

        function toggleMenu(event) {
            if (event.type === 'touchstart') event.preventDefault();
            menu.classList.toggle('active');
            btnMobile.classList.toggle('active');
            const active = menu.classList.contains('active');
            btnMobile.setAttribute('aria-expanded', active);
        }

        btnMobile.addEventListener('click', toggleMenu);
        btnMobile.addEventListener('touchstart', toggleMenu);

        menuLinks.forEach(link => {
            link.addEventListener('click', () => {
                if (menu.classList.contains('active')) {
                    menu.classList.remove('active');
                    btnMobile.classList.remove('active');
                    btnMobile.setAttribute('aria-expanded', 'false');
                }
            });
        });

        const filmTrack = document.getElementById('filmTrack');
        if (filmTrack) {
            const conteudos = Array.from(filmTrack.children);
            conteudos.forEach(item => {
                const clone = item.cloneNode(true);
                clone.setAttribute('aria-hidden', 'true');
                filmTrack.appendChild(clone);
            });
        }
    </script>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 950,
            once: true,
            offset: 75,
            easing: 'ease-out-cubic'
        });
    </script>
</body>

</html>