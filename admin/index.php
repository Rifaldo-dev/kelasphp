<?php
require_once '../inc/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

// Ambil semua materi
$materi = $db->query("SELECT * FROM materi ORDER BY created_at DESC");
?>

<?php require_once '../assets/template/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h2>Daftar Materi</h2>
  <a href="tambah.php" class="btn btn-success">+ Tambah Materi</a>
</div>

<table class="table table-bordered table-striped">
  <thead>
    <tr>
      <th>Judul</th>
      <th>Waktu</th>
      <th>Aksi</th>
    </tr>
  </thead>
  <tbody>
    <?php while ($row = $materi->fetchArray(SQLITE3_ASSOC)): ?>
    <tr>
      <td><?= htmlspecialchars($row['judul']) ?></td>
      <td><?= $row['created_at'] ?></td>
      <td>
        <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
        <a href="hapus.php?id=<?= $row['id'] ?>" onclick="return confirm('Yakin hapus?')" class="btn btn-sm btn-danger">Hapus</a>
      </td>
    </tr>
    <?php endwhile; ?>
  </tbody>
</table>

<a href="../logout.php" class="btn btn-secondary">Logout</a>

<?php require_once '../assets/template/footer.php'; ?>
