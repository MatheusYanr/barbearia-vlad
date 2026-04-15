<?php

declare(strict_types=1);

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function loadSettings(mysqli $conn): array
{
    $settings = [];
    $result = $conn->query('SELECT setting_key, setting_value FROM settings');

    if ($result instanceof mysqli_result) {
        while ($row = $result->fetch_assoc()) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }

        $result->free();
    }

    return $settings;
}

function getSetting(array $settings, string $key, string $default = ''): string
{
    if (!isset($settings[$key]) || trim((string) $settings[$key]) === '') {
        return $default;
    }

    return (string) $settings[$key];
}

function loadServices(mysqli $conn): array
{
    $services = [];

    $result = $conn->query(
        'SELECT id, name, price, is_featured
         FROM services
         WHERE is_active = 1
         ORDER BY sort_order ASC, id ASC'
    );

    if ($result instanceof mysqli_result) {
        while ($row = $result->fetch_assoc()) {
            $services[] = $row;
        }

        $result->free();
    }

    return $services;
}

function normalizeReviewPhotoPath(string $photoPath): string
{
    $photoPath = trim($photoPath);

    if ($photoPath === '') {
        return 'img/pessoa1.jpg';
    }

    if (preg_match('/^https?:\/\//i', $photoPath) === 1) {
        return $photoPath;
    }

    if (str_starts_with($photoPath, 'img/')) {
        return $photoPath;
    }

    return 'img/' . ltrim($photoPath, '/');
}

function loadReviews(mysqli $conn): array
{
    $reviews = [];

    $result = $conn->query(
        'SELECT id, client_name, quote, rating, photo_path
         FROM reviews
         WHERE is_active = 1
         ORDER BY sort_order ASC, id ASC'
    );

    if ($result instanceof mysqli_result) {
        while ($row = $result->fetch_assoc()) {
            $row['photo_path'] = normalizeReviewPhotoPath((string)($row['photo_path'] ?? ''));
            $reviews[] = $row;
        }

        $result->free();
    }

    return $reviews;
}

function formatPriceBR(float $value): string
{
    return 'R$ ' . number_format($value, 2, ',', '.');
}

function whatsappLink(string $phone): string
{
    $digits = preg_replace('/\D+/', '', $phone) ?? '';

    if ($digits === '') {
        return '#';
    }

    if (strlen($digits) <= 11) {
        $digits = '55' . $digits;
    }

    return 'https://wa.me/' . $digits;
}
