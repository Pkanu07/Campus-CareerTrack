<?php
/**
 * CareerPulse - Database Configuration and Connection Handler
 * Supports both MySQL (default for XAMPP) and SQLite (automatic seamless fallback)
 */

declare(strict_types=1);

// Prevent direct script execution if accessed in bad context
if (!defined('CAREERPULSE_APP')) {
    define('CAREERPULSE_APP', true);
}

// Configuration options
define('DB_TYPE', getenv('DB_TYPE') ?: 'auto'); // 'mysql', 'sqlite', or 'auto'
define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: 'careerpulse_db');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');
define('DB_SQLITE_PATH', __DIR__ . '/../database/careerpulse.sqlite');

/**
 * Global database connection singleton
 */
function getDB(): PDO {
    static $pdo = null;

    if ($pdo !== null) {
        return $pdo;
    }

    $driver = DB_TYPE;

    // If auto or mysql, attempt MySQL connection first
    if ($driver === 'auto' || $driver === 'mysql') {
        try {
            $dsn = sprintf('mysql:host=%s;port=%s;charset=utf8mb4', DB_HOST, DB_PORT);
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_TIMEOUT => 2, // Quick timeout to prevent hanging if server is off
            ];

            // First connect without DB name to check if DB exists or create it
            $tempPdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            $tempPdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

            // Now connect to the actual database
            $dbDsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', DB_HOST, DB_PORT, DB_NAME);
            $pdo = new PDO($dbDsn, DB_USER, DB_PASS, $options);
            $GLOBALS['ACTIVE_DB_DRIVER'] = 'mysql';
            return $pdo;
        } catch (Throwable $e) {
            if ($driver === 'mysql') {
                throw new RuntimeException("MySQL connection failed: " . $e->getMessage());
            }
            // Auto mode will fall through to SQLite
        }
    }

    // Fallback or explicit SQLite driver
    try {
        $dbDir = dirname(DB_SQLITE_PATH);
        if (!is_dir($dbDir)) {
            mkdir($dbDir, 0777, true);
        }

        $dsn = 'sqlite:' . DB_SQLITE_PATH;
        $pdo = new PDO($dsn, null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        $pdo->exec('PRAGMA foreign_keys = ON;');
        $GLOBALS['ACTIVE_DB_DRIVER'] = 'sqlite';
        return $pdo;
    } catch (Throwable $e) {
        throw new RuntimeException("Database connection error: " . $e->getMessage());
    }
}

/**
 * Execute a query with parameters and return PDOStatement
 */
function dbQuery(string $sql, array $params = []): PDOStatement {
    $db = getDB();
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

/**
 * Fetch all matching rows
 */
function dbFetchAll(string $sql, array $params = []): array {
    return dbQuery($sql, $params)->fetchAll();
}

/**
 * Fetch a single row or null
 */
function dbFetchOne(string $sql, array $params = []): ?array {
    $row = dbQuery($sql, $params)->fetch();
    return $row === false ? null : $row;
}

/**
 * Fetch a single scalar value
 */
function dbFetchValue(string $sql, array $params = []) {
    return dbQuery($sql, $params)->fetchColumn();
}

/**
 * Execute an INSERT query and return last insert ID
 */
function dbInsert(string $sql, array $params = []): int {
    $db = getDB();
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return (int)$db->lastInsertId();
}

/**
 * Execute an UPDATE or DELETE and return affected row count
 */
function dbExecute(string $sql, array $params = []): int {
    return dbQuery($sql, $params)->rowCount();
}

/**
 * Get active database driver name ('mysql' or 'sqlite')
 */
function getActiveDriver(): string {
    if (!isset($GLOBALS['ACTIVE_DB_DRIVER'])) {
        getDB();
    }
    return $GLOBALS['ACTIVE_DB_DRIVER'] ?? 'unknown';
}
