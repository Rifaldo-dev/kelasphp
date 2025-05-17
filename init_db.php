
<?php
require_once 'inc/config.php';

try {
    // Create default admin user if not exists
    $check = $db->query("SELECT COUNT(*) as count FROM users WHERE username = 'admin'");
    $row = $check->fetchArray(SQLITE3_ASSOC);
    
    if ($row['count'] == 0) {
        $password = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $db->prepare("INSERT INTO users (username, password) VALUES (:username, :password)");
        $stmt->bindValue(':username', 'admin', SQLITE3_TEXT);
        $stmt->bindValue(':password', $password, SQLITE3_TEXT);
        $stmt->execute();
        echo "Admin default berhasil dibuat\n";
    }
    
    echo "Database sudah diinisialisasi.\n";
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>
