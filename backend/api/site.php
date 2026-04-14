<?php
/**
 * API pública: retorna JSON com todo o conteúdo do site (somente leitura).
 * O front no Vercel consome este arquivo via fetch().
 */

header('Content-Type: application/json; charset=utf-8');

require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';

/*
 * CORS: defina CORS_ALLOWED_ORIGIN no config.php. Se não existir, usa * (evita fatal e tela vazia).
 */
$configured = defined('CORS_ALLOWED_ORIGIN') ? CORS_ALLOWED_ORIGIN : '*';
$reqOrigin = isset($_SERVER['HTTP_ORIGIN']) ? trim((string) $_SERVER['HTTP_ORIGIN']) : '';
if ($configured === '*' || $configured === '') {
    header('Access-Control-Allow-Origin: *');
} elseif ($reqOrigin !== '') {
    $site = rtrim(SITE_PUBLIC_URL, '/');
    $cfg = rtrim((string) $configured, '/');
    if (strcasecmp($reqOrigin, $site) === 0 || strcasecmp($reqOrigin, $cfg) === 0) {
        header('Access-Control-Allow-Origin: ' . $reqOrigin);
    } else {
        header('Access-Control-Allow-Origin: ' . $configured);
    }
} else {
    header('Access-Control-Allow-Origin: ' . $configured);
}
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Max-Age: 86400');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Método não permitido']);
    exit;
}

require_once dirname(__DIR__) . '/includes/db.php';

function settings_map($mysqli)
{
    $map = [];
    $res = $mysqli->query('SELECT setting_key, setting_value FROM settings');
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $map[$row['setting_key']] = $row['setting_value'];
        }
    }
    return $map;
}

function public_upload_url($path)
{
    $path = trim((string) $path);
    if ($path === '') {
        return '';
    }
    if (strpos($path, 'http://') === 0 || strpos($path, 'https://') === 0) {
        return $path;
    }
    if (strpos($path, 'uploads/') === 0) {
        return rtrim(SITE_PUBLIC_URL, '/') . '/' . $path;
    }
    return $path;
}

$settings = settings_map($mysqli);

$categories = [];
$catRes = $mysqli->query('SELECT id, title, icon, sort_order FROM service_categories ORDER BY sort_order ASC, id ASC');
if ($catRes) {
    while ($cat = $catRes->fetch_assoc()) {
        $cid = (int) $cat['id'];
        $items = [];
        /* Sem mysqlnd, get_result() não existe — usar id inteiro na query é seguro aqui */
        $cid_safe = (int) $cid;
        $ir = $mysqli->query(
            'SELECT id, name, price_display, sort_order FROM service_items WHERE category_id = ' . $cid_safe .
            ' ORDER BY sort_order ASC, id ASC'
        );
        if ($ir) {
            while ($row = $ir->fetch_assoc()) {
                $items[] = [
                    'id' => (int) $row['id'],
                    'name' => $row['name'],
                    'price_display' => $row['price_display'],
                ];
            }
        }
        $categories[] = [
            'id' => $cid,
            'title' => $cat['title'],
            'icon' => $cat['icon'],
            'items' => $items,
        ];
    }
}

$special = [];
$sRes = $mysqli->query('SELECT id, title, hours_text, sort_order FROM special_hours ORDER BY sort_order ASC, id ASC');
if ($sRes) {
    while ($row = $sRes->fetch_assoc()) {
        $special[] = [
            'id' => (int) $row['id'],
            'title' => $row['title'],
            'hours_text' => $row['hours_text'],
        ];
    }
}

$reviews = [];
$rRes = $mysqli->query('SELECT id, author_name, quote, stars, photo_path, sort_order FROM reviews ORDER BY sort_order ASC, id ASC');
if ($rRes) {
    while ($row = $rRes->fetch_assoc()) {
        $reviews[] = [
            'id' => (int) $row['id'],
            'author_name' => $row['author_name'],
            'quote' => $row['quote'],
            'stars' => (int) $row['stars'],
            'photo_url' => public_upload_url($row['photo_path']),
        ];
    }
}

$gallery = [];
$gRes = $mysqli->query('SELECT id, filename, caption, sort_order FROM gallery ORDER BY sort_order ASC, id ASC');
if ($gRes) {
    while ($row = $gRes->fetch_assoc()) {
        $gallery[] = [
            'id' => (int) $row['id'],
            'caption' => $row['caption'],
            'image_url' => rtrim(SITE_PUBLIC_URL, '/') . '/uploads/' . rawurlencode($row['filename']),
        ];
    }
}

$price_rows = [];
$pRes = $mysqli->query('SELECT si.name, si.price_display, sc.title AS category_title FROM service_items si JOIN service_categories sc ON sc.id = si.category_id WHERE si.price_display IS NOT NULL AND si.price_display <> \'\' ORDER BY sc.sort_order, si.sort_order');
if ($pRes) {
    while ($row = $pRes->fetch_assoc()) {
        $price_rows[] = [
            'category' => $row['category_title'],
            'name' => $row['name'],
            'price' => $row['price_display'],
        ];
    }
}

$out = [
    'settings' => $settings,
    'service_categories' => $categories,
    'special_hours' => $special,
    'reviews' => $reviews,
    'gallery' => $gallery,
    'price_list' => $price_rows,
    'public_uploads_base' => rtrim(SITE_PUBLIC_URL, '/') . '/uploads/',
];

echo json_encode($out, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
