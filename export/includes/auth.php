<?php
/**
 * Authentication & Session Helper
 * Local Shop OS
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if a tenant admin is logged in
 */
function is_tenant_logged_in(): bool {
    return isset($_SESSION['user_id']) && $_SESSION['role'] === 'tenant_admin' && !empty($_SESSION['tenant_id']);
}

/**
 * Get current logged in tenant ID
 */
function get_logged_tenant_id(): ?int {
    return is_tenant_logged_in() ? (int)$_SESSION['tenant_id'] : null;
}

/**
 * Check if a super admin is logged in
 */
function is_super_admin_logged_in(): bool {
    return !empty($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'super_admin' && empty($_SESSION['tenant_id']);
}

/**
 * Require tenant admin login or redirect to login page
 */
function require_tenant_auth(): void {
    if (!is_tenant_logged_in()) {
        header("Location: /login.php");
        exit;
    }
}

/**
 * Require super admin login or redirect to admin login page
 */
function require_super_admin_auth(): void {
    if (!is_super_admin_logged_in()) {
        header("Location: /admin/login.php");
        exit;
    }
}

/**
 * Slugify a string for subdomain auto-generation
 */
function slugify(string $text): string {
    // replace non letter or digits by -
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    // transliterate
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    // remove unwanted characters
    $text = preg_replace('~[^-\w]+~', '', $text);
    // trim
    $text = trim($text, '-');
    // remove duplicate -
    $text = preg_replace('~-+~', '-', $text);
    // lowercase
    $text = strtolower($text);

    if (empty($text)) {
        return 'shop-' . rand(100, 999);
    }
    return $text;
}

/**
 * Helper to set flash messages
 */
function set_flash(string $type, string $message): void {
    $_SESSION['flash'] = [
        'type' => $type, // 'success', 'error', 'warning', 'info'
        'message' => $message
    ];
}

/**
 * Helper to set flash messages or respond via JSON if AJAX request
 */
function respond_flash(string $type, string $message, string $redirectUrl = '', array $extraData = []): void {
    $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') || 
              isset($_POST['is_ajax']);

    if ($isAjax) {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');
        echo json_encode(array_merge([
            'success' => ($type === 'success'),
            'message' => $message,
            'error'   => ($type === 'error' ? $message : null)
        ], $extraData));
        exit;
    }

    set_flash($type, $message);
    if (!empty($redirectUrl)) {
        header("Location: " . $redirectUrl);
        exit;
    }
}

/**
 * Helper to display flash message
 */
function display_flash(): void {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        $bgColor = 'bg-blue-500';
        if ($flash['type'] === 'success') $bgColor = 'bg-emerald-600';
        if ($flash['type'] === 'error') $bgColor = 'bg-rose-600';
        if ($flash['type'] === 'warning') $bgColor = 'bg-amber-600';

        echo '<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            <div class="' . $bgColor . ' text-white px-4 py-3 rounded-xl shadow-lg flex items-center justify-between transition-all duration-300">
                <div class="flex items-center space-x-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span class="font-medium">' . htmlspecialchars($flash['message']) . '</span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-white hover:text-gray-200 font-bold ml-4">&times;</button>
            </div>
        </div>';
    }
}
