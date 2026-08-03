<?php
/**
 * Database Connection & Multi-Tenant Data Access Layer
 * Local Shop OS
 */

define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'local_shop_os');
define('DB_USER', 'root');
define('DB_PASS', '');

function getDBConnection(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . (DB_HOST !== 'localhost' ? ";port=" . DB_PORT : "") . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die("Database connection failed: " . htmlspecialchars($e->getMessage()));
        }
    }
    return $pdo;
}

/**
 * Enforced Tenant Data Access Layer
 * Enforces tenant_id scoping for all database operations on tenant-owned tables.
 */
class TenantDB {
    
    /**
     * Executes a SELECT query scoped by tenant_id
     */
    public static function fetchAll(PDO $pdo, int $tenantId, string $sql, array $params = []): array {
        self::verifyTenantScoping($sql);
        $stmt = $pdo->prepare($sql);
        // Bind tenant_id explicitly if tenant parameter present, or ensure query includes tenant_id
        $stmt->execute(array_merge(['tenant_id' => $tenantId], $params));
        return $stmt->fetchAll();
    }

    public static function fetchOne(PDO $pdo, int $tenantId, string $sql, array $params = []) {
        self::verifyTenantScoping($sql);
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array_merge(['tenant_id' => $tenantId], $params));
        return $stmt->fetch();
    }

    /**
     * Verifies at the data-access layer that the query contains tenant_id
     */
    private static function verifyTenantScoping(string $sql): void {
        if (stripos($sql, 'tenant_id') === false) {
            throw new Exception("Security Violation: Query touches tenant data without explicit 'tenant_id' scope!");
        }
    }
}
