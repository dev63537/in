<?php
// ============================================================
// includes/db.php — PDO Database Connection (Singleton)
// Uses PDO with prepared statements for security
// ============================================================

require_once __DIR__ . '/../config.php';

function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            if (DEBUG_MODE) {
                die("Database Connection Failed: " . $e->getMessage());
            } else {
                die("Service temporarily unavailable. Please try again later.");
            }
        }
    }
    return $pdo;
}

// Helper: fetch all rows
function dbFetchAll($sql, $params = []) {
    $stmt = getDB()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

// Helper: fetch one row
function dbFetchOne($sql, $params = []) {
    $stmt = getDB()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetch();
}

// Helper: execute (insert/update/delete)
function dbExecute($sql, $params = []) {
    $stmt = getDB()->prepare($sql);
    return $stmt->execute($params);
}

// Helper: last insert ID
function dbLastId() {
    return getDB()->lastInsertId();
}
