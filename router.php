<?php
/**
 * Local Router for PHP Built-in Development Server
 * LocalShopOS
 */

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$file = __DIR__ . $uri;

// 1. If directory requested, check for index.php
if (is_dir($file)) {
    $indexPath = rtrim($file, '/') . '/index.php';
    if (file_exists($indexPath)) {
        require $indexPath;
        return true;
    }
}

// 2. If static file exists (CSS, JS, images), serve directly
if ($uri !== '/' && file_exists($file)) {
    return false;
}

// 3. Clean Storefront URL mapping (e.g., /ramu-kirana => shop.php?subdomain=ramu-kirana)
$trimmed = trim($uri, '/');
if (!empty($trimmed) && preg_match('/^[a-zA-Z0-9-]+$/', $trimmed)) {
    $_GET['subdomain'] = $trimmed;
    require __DIR__ . '/shop.php';
    return true;
}

// 4. Fallback to main landing page
require __DIR__ . '/index.php';
