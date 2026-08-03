<?php
/**
 * Database Connection & Multi-Tenant Data Access Layer
 * Local Shop OS
 */

define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_NAME', 'local_shop_os');
define('DB_USER', 'root');
define('DB_PASS', '');

function getDBConnection(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Attempt auto database creation if database missing
            try {
                $rootDsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";charset=utf8mb4";
                $tmpPdo = new PDO($rootDsn, DB_USER, DB_PASS);
                $tmpPdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                
                $sqlFile = __DIR__ . '/../schema.sql';
                if (file_exists($sqlFile)) {
                    $tmpPdo->exec("USE `" . DB_NAME . "`");
                    $tmpPdo->exec(file_get_contents($sqlFile));
                }
                
                $pdo = new PDO("mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
            } catch (PDOException $ex) {
                die("Database connection failed: " . htmlspecialchars($ex->getMessage()));
            }
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
