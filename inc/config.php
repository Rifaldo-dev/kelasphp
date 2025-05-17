
<?php
if (!headers_sent() && session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    $db_path = __DIR__ . '/../db/materi.db';
    $db_dir = dirname($db_path);
    
    if (!file_exists($db_dir)) {
        mkdir($db_dir, 0777, true);
    }
    
    $db = new SQLite3($db_path);
    $db->exec('PRAGMA foreign_keys = ON');
    $db->exec('PRAGMA encoding = "UTF-8"');
    
    // Fix table structure with created_at column
    $db->exec('CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT UNIQUE NOT NULL,
        password TEXT NOT NULL
    )');
    
    $db->exec('CREATE TABLE IF NOT EXISTS materi (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        judul TEXT NOT NULL,
        konten TEXT,
        gambar TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )');
    
} catch (Exception $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}
?>
