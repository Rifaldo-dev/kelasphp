<?php
require_once 'inc/config.php';
require_once 'assets/template/header.php';

// Include the Material class
require_once 'class/Material.php';

try {
    // Ambil data pencarian dari form
    $search = isset($_GET['search']) ? '%' . $_GET['search'] . '%' : '%';

    // Tentukan jumlah materi per halaman
    $limit = 6;
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $offset = ($page - 1) * $limit;

    // Instantiate the Material class
    $material = new Material($db);

    // Query untuk mengambil data materi dengan pencarian dan pagination
    $result = $material->getAllMaterials($search, $limit, $offset);

    // Hitung total materi untuk pagination
    $countResult = $db->query("SELECT COUNT(*) as total FROM materi WHERE judul LIKE '$search'");
    $totalMateri = $countResult->fetchArray(SQLITE3_ASSOC)['total'];
    $totalPages = ceil($totalMateri / $limit);
    ?>

    <div class="container py-4 py-lg-5">
        <!-- Form Pencarian -->
        <div class="row mb-5">
            <div class="col-lg-8 mx-auto text-center">
                <h1 class="display-4 fw-bold mb-3">Daftar Materi</h1>
                <p class="lead text-muted">Kumpulan materi pembelajaran yang tersedia untuk Anda pelajari</p>

                <!-- Pencarian -->
                <form method="get" class="mb-4">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="Cari materi..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                </form>
                <div class="border-bottom border-primary w-25 mx-auto my-4" style="border-width: 3px !important;"></div>
            </div>
        </div>

        <!-- Daftar Kartu Materi -->
        <div class="row g-4">
            <?php 
            $hasData = false;
            while ($row = $result->fetchArray(SQLITE3_ASSOC)): 
                $hasData = true;

                // Format tanggal
                $date = !empty($row['created_at']) ? new DateTime($row['created_at']) : null;
                $formattedDate = $date ? $date->format('d M Y') : 'Tanggal tidak tersedia';
            ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 shadow-sm border-0 rounded-3 overflow-hidden transition-all hover-shadow">
                        <!-- Gambar Materi -->
                        <?php if (!empty($row['gambar'])): ?>
                            <div class="card-img-container" style="height: 200px; overflow: hidden;">
                                <img src="<?= htmlspecialchars($row['gambar']) ?>" class="card-img-top h-100 object-fit-cover" alt="<?= htmlspecialchars($row['judul']) ?>">
                            </div>
                        <?php else: ?>
                            <div class="card-img-container bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                <i class="bi bi-journal-text" style="font-size: 3rem;"></i>
                            </div>
                        <?php endif; ?>

                        <!-- Konten Kartu -->
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex align-items-center mb-2">
                                <small class="text-muted">
                                    <i class="bi bi-calendar-date me-1"></i><?= $formattedDate ?>
                                </small>
                            </div>
                            <h5 class="card-title fw-bold"><?= htmlspecialchars($row['judul']) ?></h5>
                            <p class="card-text text-muted flex-grow-1">
                                <?= substr(strip_tags($row['konten'] ?? ''), 0, 100) ?>...
                            </p>
                            <a href="baca.php?id=<?= $row['id'] ?>" class="btn btn-primary rounded-pill mt-3 d-flex align-items-center justify-content-center gap-2">
                                <span>Baca Selengkapnya</span>
                                <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>

            <!-- Jika Tidak Ada Data -->
            <?php if (!$hasData): ?>
                <div class="col-12">
                    <div class="alert alert-info shadow-sm border-0 rounded-3 p-4 d-flex align-items-center">
                        <i class="bi bi-info-circle-fill me-3 fs-4"></i>
                        <div>
                            <h5 class="alert-heading">Belum Ada Materi</h5>
                            <p class="mb-0">Saat ini belum ada materi yang tersedia. Silakan cek kembali nanti.</p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-center mt-5">
            <nav aria-label="Page navigation">
                <ul class="pagination">
                    <li class="page-item <?= $page == 1 ? 'disabled' : '' ?>">
                        <a class="page-link" href="?search=<?= htmlspecialchars($_GET['search'] ?? '') ?>&page=1">First</a>
                    </li>
                    <li class="page-item <?= $page == 1 ? 'disabled' : '' ?>">
                        <a class="page-link" href="?search=<?= htmlspecialchars($_GET['search'] ?? '') ?>&page=<?= $page - 1 ?>">Previous</a>
                    </li>
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                            <a class="page-link" href="?search=<?= htmlspecialchars($_GET['search'] ?? '') ?>&page=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?= $page == $totalPages ? 'disabled' : '' ?>">
                        <a class="page-link" href="?search=<?= htmlspecialchars($_GET['search'] ?? '') ?>&page=<?= $page + 1 ?>">Next</a>
                    </li>
                    <li class="page-item <?= $page == $totalPages ? 'disabled' : '' ?>">
                        <a class="page-link" href="?search=<?= htmlspecialchars($_GET['search'] ?? '') ?>&page=<?= $totalPages ?>">Last</a>
                    </li>
                </ul>
            </nav>
        </div>

    </div>

    <?php
} catch (Exception $e) {
    // Tampilkan pesan error jika query gagal
    echo '<div class="container py-4 py-lg-5">
            <div class="alert alert-danger shadow-sm border-0 rounded-3 p-4">
                <div class="d-flex">
                    <div class="me-3">
                        <i class="bi bi-exclamation-triangle-fill fs-1"></i>
                    </div>
                    <div>
                        <h4 class="alert-heading">Terjadi Kesalahan</h4>
                        <p class="mb-0">Error: ' . htmlspecialchars($e->getMessage()) . '</p>
                    </div>
                </div>
            </div>
          </div>';
}

// Tambahkan Bootstrap Icons
echo '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">';

// Tambahkan gaya tambahan
echo '<style>
    .hover-shadow:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
    }
    .transition-all {
        transition: all 0.3s ease;
    }
    .object-fit-cover {
        object-fit: cover;
    }
</style>';

require_once 'assets/template/footer.php';
?>