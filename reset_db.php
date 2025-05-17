
<?php
require_once 'inc/config.php';

try {
    // Hapus semua data dari tabel materi
    $db->exec('DELETE FROM materi');
    
    // Reset auto increment
    $db->exec('DELETE FROM sqlite_sequence WHERE name="materi"');
    
    echo "Database berhasil direset!\n";
    echo "Silakan kembali ke halaman login dan coba masuk kembali.";
    
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>
