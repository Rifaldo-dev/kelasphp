
<?php
require_once '../inc/config.php';
require_once '../inc/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = trim($_POST['judul'] ?? '');
    $konten = $_POST['konten'] ?? '';
    
    // Handle file upload
    $gambar = '';
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === 0) {
        $upload_dir = '../db/upload/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_name = time() . '_' . $_FILES['gambar']['name'];
        $target_file = $upload_dir . $file_name;
        
        if (move_uploaded_file($_FILES['gambar']['tmp_name'], $target_file)) {
            $gambar = 'db/upload/' . $file_name;
        }
    }
    
    $stmt = $db->prepare("INSERT INTO materi (judul, konten, gambar) VALUES (?, ?, ?)");
    $stmt->bindValue(1, $judul);
    $stmt->bindValue(2, $konten);
    $stmt->bindValue(3, $gambar);
    
    if ($stmt->execute()) {
        header("Location: index.php");
        exit;
    }
}

require_once '../assets/template/header.php';
?>

<div class="container mt-4">
    <h2>Tambah Materi Baru</h2>
    
    <form method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label class="form-label">Judul</label>
            <input type="text" name="judul" class="form-control" required>
        </div>
        
        <div class="mb-3">
            <label class="form-label">Gambar</label>
            <input type="file" name="gambar" class="form-control" accept="image/*">
        </div>
        
        <div class="mb-3">
            <label class="form-label">Konten</label>
            <textarea name="konten" id="editor" class="form-control"></textarea>
        </div>
        
        <button type="submit" class="btn btn-primary">Simpan</button>
        <a href="index.php" class="btn btn-secondary">Batal</a>
    </form>
</div>

<script src="https://cdn.tiny.cloud/1/v49oxpc6r97soluop7wt7y81m0u6w6ydxcqhwhmj9zxk4nkx/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
tinymce.init({
    selector: '#editor',
    plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
    toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table | align lineheight | numlist bullist indent outdent | emoticons charmap | removeformat',
    images_upload_url: 'upload.php',
    images_upload_base_path: '/',
    height: 500
});
</script>

<?php require_once '../assets/template/footer.php'; ?>
