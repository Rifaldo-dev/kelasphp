<?php
include('../inc/config.php');

// Pastikan folder 'uploads' ada
$folder_upload = __DIR__ . '/uploads/';
if (!is_dir($folder_upload)) {
    mkdir($folder_upload, 0777, true);
}

$judul = $_POST['judul'] ?? '';
$konten = $_POST['konten'] ?? '';
$gambar = '';

// Proses upload gambar
if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === 0) {
    $nama_file = basename($_FILES['gambar']['name']);
    $lokasi = $folder_upload . $nama_file;

    if (move_uploaded_file($_FILES['gambar']['tmp_name'], $lokasi)) {
        $gambar = $nama_file;
    }
}

// Simpan ke database SQLite (ubah 'artikel' menjadi 'materi')
$stmt = $db->prepare("INSERT INTO materi (judul, konten, gambar) VALUES (:judul, :konten, :gambar)");
$stmt->bindValue(':judul', $judul);
$stmt->bindValue(':konten', $konten);
$stmt->bindValue(':gambar', $gambar);
$result = $stmt->execute();

if ($result) {
    echo "✅ Artikel berhasil disimpan.";
} else {
    echo "❌ Gagal menyimpan artikel.";
}
?>
