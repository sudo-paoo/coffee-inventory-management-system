<?php
// !URL helpers - Configuration for production
// function base_url($path = '') {
//     $base = '/';
//     return $base . ltrim($path, '/');
// }

// URL helpers - Configuration for local
function base_url($path = '') {
    $base = '/coffee-inventory-management-system/public';
    return $base . '/' . ltrim($path, '/');
}

function asset_url($path) {
    return base_url('assets/' . ltrim($path, '/'));
}

function css_url($file) {
    return base_url('css/' . ltrim($file, '/'));
}

function redirect($page) {
    header('Location: ' . base_url($page));
    exit;
}

// Security helpers
function escape($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

// Format helpers
function format_currency($amount) {
    return '₱ ' . number_format($amount, 2);
}

function time_ago($datetime, $timezone = 'Asia/Manila') {
    // Set default timezone
    date_default_timezone_set($timezone);
    
    // Create DateTime objects
    $now = new DateTime('now', new DateTimeZone($timezone));
    $past = new DateTime($datetime);
    $past->setTimezone(new DateTimeZone($timezone));

    $diff = $now->getTimestamp() - $past->getTimestamp();

    if ($diff < 0) $diff = 0; // Handle future dates
    if ($diff < 60) return 'just now';
    if ($diff < 3600) return floor($diff / 60) . ' minutes ago';
    if ($diff < 86400) return floor($diff / 3600) . ' hours ago';
    return floor($diff / 86400) . ' days ago';
}


// View rendering
function render_view($view, $data = []) {
    extract($data);
    require __DIR__ . '/../views/' . $view;
}
?>
