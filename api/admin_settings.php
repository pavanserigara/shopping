<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

// Super admin authentication check
if (!is_super_admin_logged_in()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized: Super Admin session required']);
    exit;
}

$pdo = getDBConnection();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

$siteName       = trim($_POST['site_name'] ?? '');
$supportContact = trim($_POST['support_contact_number'] ?? '');
$whatsappContact = trim($_POST['whatsapp_contact'] ?? '');
$siteLogoUrl    = trim($_POST['site_logo_url'] ?? '/assets/logo.png');
$primaryColor   = trim($_POST['primary_color'] ?? '#f5b400');
$accentColor    = trim($_POST['accent_color'] ?? '#f5b400');

$errors = [];

if (empty($siteName)) {
    $errors[] = "Site Name cannot be empty.";
}
if (empty($supportContact)) {
    $errors[] = "Support contact number cannot be empty.";
}
if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $primaryColor)) {
    $errors[] = "Primary color must be a valid 6-digit HEX color code (e.g. #F5B400).";
}
if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $accentColor)) {
    $errors[] = "Accent color must be a valid 6-digit HEX color code (e.g. #F5B400).";
}

if (!empty($errors)) {
    echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
    exit;
}

try {
    $stmt = $pdo->prepare("
        UPDATE platform_settings SET 
            site_name = ?, 
            support_contact_number = ?, 
            whatsapp_contact = ?, 
            site_logo_url = ?, 
            primary_color = ?, 
            accent_color = ? 
        WHERE id = 1
    ");
    $stmt->execute([
        $siteName,
        $supportContact,
        $whatsappContact,
        $siteLogoUrl,
        $primaryColor,
        $accentColor
    ]);

    // Fetch updated row from DB to return confirmed values
    $fresh = $pdo->query("SELECT site_name, support_contact_number, whatsapp_contact, site_logo_url, primary_color, accent_color, updated_at FROM platform_settings WHERE id = 1")->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'message' => 'Platform settings saved successfully.',
        'data'    => $fresh
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
