<?php
try {
    $dbPath = __DIR__ . '/../data/database.db';
    $dir = dirname($dbPath);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    $pdo = new PDO('sqlite:' . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

    // Stability + performance under concurrent requests (best effort)
    foreach ([
        "PRAGMA foreign_keys = ON",
        "PRAGMA busy_timeout = 5000",
        "PRAGMA journal_mode = DELETE",
        "PRAGMA synchronous = NORMAL",
        "PRAGMA temp_store = MEMORY",
    ] as $pragma) {
        try {
            $pdo->exec($pragma);
        } catch (Throwable $e) {
            // Some environments (shared hosting/readonly fs) reject specific pragmas.
            // Ignore and continue with defaults instead of failing hard.
        }
    }

    return $pdo;
} catch (PDOException $e) {
    die("Fehler bei der Verbindung zur Datenbank: " . $e->getMessage());
}
