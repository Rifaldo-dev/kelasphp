<?php
require_once '../inc/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$id = $_GET['id'] ?? null;
if ($id) {
    $stmt = $db->prepare("DELETE FROM materi WHERE id = ?");
    $stmt->bindValue(1, $id);
    $stmt->execute();
}

header("Location: index.php");
exit;
