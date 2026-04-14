/* global API_BASE */

function escapeHtml(text) {
    var s = String(text == null ? '' : text);
    var map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
    return s.replace(/[&<>"']/g, function (m) { return map[m]; });
}

function setText(id, text) {
    var el = document.getElementById(id);
    if (el) el.textContent = text == null ? '' : text;
}

function setHtml(id, html) {
    var el = document.getElementById(id);
    if (el) el.innerHTML = html;
}

var ICONS = {
    cut: '<svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-icon" aria-hidden="true"><circle cx="6" cy="6" r="3"></circle><circle cx="6" cy="18" r="3"></circle><line x1="20" y1="4" x2="8.12" y2="15.88"></line><line x1="14.47" y1="14.48" x2="20" y2="20"></line><line x1="8.12" y1="8.12" x2="12" y2="12"></line></svg>',
    blade: '<svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-icon" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2" ry="2"></rect><path d="M3 12h5m8 0h5M12 9v6"></path><circle cx="12" cy="12" r="2"></circle></svg>',
    package: '<svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-icon" aria-hidden="true"><line x1="16.5" y1="9.4" x2="7.5" y2="4.21"></line><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>'
};

function iconSvg(key) {
    return ICONS[key] || ICONS.cut;
}

function renderServicos(root, categories) {
    root.innerHTML = '';
    var delay = 100;
    categories.forEach(function (cat) {
        var art = document.createElement('article');
        art.className = 'coluna';
        art.setAttribute('data-aos', 'zoom-in');
        art.setAttribute('data-aos-delay', String(delay));
        delay += 100;

        art.innerHTML = iconSvg(cat.icon) +
            '<h3>' + escapeHtml(cat.title) + '</h3><ul></ul>';
        var ul = art.querySelector('ul');
        (cat.items || []).forEach(function (it) {
            var li = document.createElement('li');
            var line = escapeHtml(it.name);
            if (it.price_display) {
                line += ' <span class="preco-inline">(' + escapeHtml(it.price_display) + ')</span>';
            }
            li.innerHTML = line;
            ul.appendChild(li);
        });
        root.appendChild(art);
    });
}

function renderPrecos(tbody, rows) {
    tbody.innerHTML = '';
    if (!rows || rows.length === 0) {
        var tr = document.createElement('tr');
        var td = document.createElement('td');
        td.colSpan = 3;
        td.textContent = 'Nenhum preço cadastrado ainda.';
        tr.appendChild(td);
        tbody.appendChild(tr);
        return;
    }
    rows.forEach(function (r) {
        var tr = document.createElement('tr');
        tr.innerHTML = '<td>' + escapeHtml(r.category) + '</td>' +
            '<td>' + escapeHtml(r.name) + '</td>' +
            '<td>' + escapeHtml(r.price) + '</td>';
        tbody.appendChild(tr);
    });
}

function renderEquipe(root, s) {
    var wppIcon = 'https://upload.wikimedia.org/wikipedia/commons/6/6b/WhatsApp.svg';
    root.innerHTML = '';

    function card(nome, tel, waDigits) {
        var art = document.createElement('article');
        art.className = 'membro';
        art.setAttribute('data-aos', 'flip-left');
        var a = document.createElement('a');
        a.href = 'https://wa.me/' + waDigits;
        a.target = '_blank';
        a.rel = 'noopener noreferrer';
        a.className = 'wpp-contato';
        a.setAttribute('aria-label', 'WhatsApp ' + nome);
        a.innerHTML = '<img src="' + wppIcon + '" alt="" class="wpp-icon" loading="lazy"> ' + escapeHtml(tel);
        var p = document.createElement('p');
        p.appendChild(a);
        art.innerHTML = '<h3>' + escapeHtml(nome) + '</h3>';
        art.appendChild(p);
        return art;
    }

    var v = card(s.team_vlad_name, s.team_vlad_phone_display, s.team_vlad_wa_digits);
    v.setAttribute('data-aos-delay', '100');
    root.appendChild(v);
    var j = card(s.team_junior_name, s.team_junior_phone_display, s.team_junior_wa_digits);
    j.setAttribute('data-aos', 'flip-right');
    j.setAttribute('data-aos-delay', '200');
    root.appendChild(j);
}

function renderGaleria(root, items) {
    root.innerHTML = '';
    if (!items || items.length === 0) {
        var p = document.createElement('p');
        p.className = 'galeria-vazia';
        p.textContent = 'Fotos em breve.';
        root.appendChild(p);
        return;
    }
    items.forEach(function (it) {
        var fig = document.createElement('figure');
        fig.className = 'galeria-item';
        var img = document.createElement('img');
        img.src = it.image_url;
        img.alt = it.caption || 'Foto da galeria';
        img.loading = 'lazy';
        var cap = document.createElement('figcaption');
        cap.textContent = it.caption || '';
        fig.appendChild(img);
        fig.appendChild(cap);
        root.appendChild(fig);
    });
}

function starsHtml(n) {
    var c = Math.max(1, Math.min(5, parseInt(n, 10) || 5));
    var out = '';
    for (var i = 0; i < c; i++) out += '\u2605';
    return out;
}

function renderAvaliacoes(track, reviews) {
    track.innerHTML = '';
    if (!reviews || reviews.length === 0) {
        var fig = document.createElement('figure');
        fig.className = 'avaliacao';
        fig.innerHTML = '<div class="estrelas" aria-hidden="true">\u2605\u2605\u2605\u2605\u2605</div>' +
            '<blockquote>Nenhuma avaliação cadastrada.</blockquote>' +
            '<figcaption class="cliente-info"><span>-</span></figcaption>';
        track.appendChild(fig);
        return;
    }
    reviews.forEach(function (r) {
        var fig = document.createElement('figure');
        fig.className = 'avaliacao';
        var stars = starsHtml(r.stars);
        fig.innerHTML =
            '<div class="estrelas" aria-label="Avaliação de ' + String(r.stars) + ' estrelas">' + stars + '</div>' +
            '<blockquote>&ldquo;' + escapeHtml(r.quote) + '&rdquo;</blockquote>' +
            '<figcaption class="cliente-info">' +
            '<img class="cliente-avatar" src="' + escapeHtml(r.photo_url) + '" alt="">' +
            '<span>- ' + escapeHtml(r.author_name) + '</span></figcaption>';
        track.appendChild(fig);
    });

    var nodes = Array.prototype.slice.call(track.children);
    nodes.forEach(function (item) {
        var clone = item.cloneNode(true);
        clone.setAttribute('aria-hidden', 'true');
        track.appendChild(clone);
    });
}

function fillBannerHours(s, specials) {
    var def = document.getElementById('banner-hours-default');
    var sp = document.getElementById('banner-hours-special');
    if (def) {
        def.innerHTML = '';
        var p1 = document.createElement('p');
        p1.textContent = s.hours_weekday || '';
        var p2 = document.createElement('p');
        p2.textContent = s.hours_saturday || '';
        def.appendChild(p1);
        def.appendChild(p2);
    }
    if (sp) {
        sp.innerHTML = '';
        (specials || []).forEach(function (row) {
            var p = document.createElement('p');
            p.innerHTML = '<strong>' + escapeHtml(row.title) + ':</strong> ' + escapeHtml(row.hours_text);
            sp.appendChild(p);
        });
    }
}

function initAfterPaint() {
    if (window.AOS) {
        window.AOS.refresh();
    }
}

async function loadSite() {
    var errEl = document.getElementById('api-error');
    if (typeof API_BASE === 'undefined' || !API_BASE || /SEU-/i.test(API_BASE)) {
        errEl.hidden = false;
        errEl.textContent = 'Configure API_BASE em frontend/config.js com a URL da pasta api do seu PHP.';
        return;
    }

    var path = API_BASE.replace(/\/$/, '') + '/site.php';
    var url = path;
    if (path.indexOf('http') !== 0 && typeof window !== 'undefined' && window.location && window.location.origin) {
        url = window.location.origin + (path.indexOf('/') === 0 ? '' : '/') + path;
    }
    var res;
    try {
        res = await fetch(path, { credentials: 'omit', cache: 'no-store' });
    } catch (e) {
        errEl.hidden = false;
        var det = (e && e.message) ? e.message : String(e);
        errEl.innerHTML = 'Não foi possível carregar a API. URL tentada: <code>' + escapeHtml(url) + '</code>. ' +
            'Abra essa URL em uma aba: se não aparecer JSON, o PHP ou o banco falhou. ' +
            'Se o site e a API forem domínios diferentes, em <code>backend/config.php</code> use ' +
            '<code>CORS_ALLOWED_ORIGIN</code> com <code>*</code> (só para teste) ou a URL exata do site. ' +
            '<small>(' + escapeHtml(det) + ')</small>';
        return;
    }
    if (!res.ok) {
        errEl.hidden = false;
        errEl.textContent = 'A API respondeu com erro HTTP ' + res.status + '.';
        return;
    }

    var data;
    try {
        data = await res.json();
    } catch (e) {
        errEl.hidden = false;
        errEl.textContent = 'Resposta inválida da API (não é JSON).';
        return;
    }
    errEl.hidden = true;

    var s = data.settings || {};
    var meta = document.querySelector('meta[name="description"]');
    if (meta && s.meta_description) meta.setAttribute('content', s.meta_description);

    var ctaImg = document.getElementById('cta-img');
    if (ctaImg && s.banner_image) ctaImg.src = s.banner_image;

    var wa = document.getElementById('cta-wa');
    if (wa && s.whatsapp_main_digits) {
        wa.href = 'https://wa.me/' + s.whatsapp_main_digits;
    }

    fillBannerHours(s, data.special_hours);

    var sobreImg = document.getElementById('sobre-img');
    if (sobreImg && s.about_image) sobreImg.src = s.about_image;
    setText('sobre-texto', s.about_text || '');

    renderServicos(document.getElementById('servicos-root'), data.service_categories || []);
    renderPrecos(document.getElementById('precos-tbody'), data.price_list || []);
    renderEquipe(document.getElementById('equipe-root'), s);
    renderGaleria(document.getElementById('galeria-root'), data.gallery || []);

    setText('localizacao-intro', s.address_intro || '');
    var end = document.getElementById('localizacao-endereco');
    if (end) {
        end.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-icon-inline" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg> <strong>Endereço:</strong> ' + (s.address_line || '');
    }
    var iframe = document.getElementById('map-iframe');
    if (iframe && s.map_iframe_src) iframe.src = s.map_iframe_src;

    renderAvaliacoes(document.getElementById('filmTrack'), data.reviews || []);

    setText('footer-tagline', s.footer_tagline || '');
    setText('footer-a1', s.footer_address_1 || '');
    setText('footer-a2', s.footer_address_2 || '');
    setText('footer-a3', s.footer_address_3 || '');
    setText('footer-p1', s.team_vlad_phone_display || '');
    setText('footer-p2', s.team_junior_phone_display || '');
    setText('footer-h1', s.footer_hours_1 || '');
    setText('footer-h2', s.footer_hours_2 || '');

    window.requestAnimationFrame(function () {
        initAfterPaint();
    });
}

document.addEventListener('DOMContentLoaded', loadSite);
