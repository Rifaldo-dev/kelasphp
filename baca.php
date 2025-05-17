<?php
require_once 'inc/config.php';
require_once 'assets/template/header.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: index.php");
    exit;
}

try {
    // Get the requested material
    $stmt = $db->prepare("SELECT * FROM materi WHERE id = ?");
    $stmt->bindValue(1, $id);
    $result = $stmt->execute();
    $row = $result->fetchArray(SQLITE3_ASSOC);

    if (!$row) {
        throw new Exception("Materi tidak ditemukan.");
    }

    // Check if view_count column exists in the table
    $tableInfo = $db->query("PRAGMA table_info(materi)");
    $hasViewCount = false;
    while ($column = $tableInfo->fetchArray(SQLITE3_ASSOC)) {
        if ($column['name'] === 'view_count') {
            $hasViewCount = true;
            break;
        }
    }

    // Update view count if the column exists
    $viewCount = 0;
    if ($hasViewCount) {
        $viewCount = ($row['view_count'] ?? 0) + 1;
        $updateStmt = $db->prepare("UPDATE materi SET view_count = ? WHERE id = ?");
        $updateStmt->bindValue(1, $viewCount);
        $updateStmt->bindValue(2, $id);
        $updateStmt->execute();
    } else {
        // If view_count doesn't exist, we'll just display 1 for the current view
        $viewCount = 1;
    }

    // Get related materials (same category if available)
    $category = $row['kategori'] ?? '';
    $relatedResult = null;
    
    if (!empty($category)) {
        $relatedStmt = $db->prepare("SELECT id, judul, gambar, created_at FROM materi WHERE kategori = ? AND id != ? ORDER BY created_at DESC LIMIT 3");
        $relatedStmt->bindValue(1, $category);
        $relatedStmt->bindValue(2, $id);
        $relatedResult = $relatedStmt->execute();
    } else {
        // If no category, get recent materials
        $relatedResult = $db->query("SELECT id, judul, gambar, created_at FROM materi WHERE id != {$id} ORDER BY created_at DESC LIMIT 3");
    }

    // Format date
    $createdDate = new DateTime($row['created_at']);
    $formattedDate = $createdDate->format('d F Y');
?>

<div id="app-container" class="min-vh-100 transition-all">
    <!-- Article Header -->
    <div class="article-header bg-gradient-primary text-white position-relative">
        <div class="container py-5">
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="index.php" class="text-white opacity-75">Beranda</a></li>
                            <?php if (!empty($category)): ?>
                                <li class="breadcrumb-item"><a href="index.php?category=<?= urlencode($category) ?>" class="text-white opacity-75"><?= htmlspecialchars($category) ?></a></li>
                            <?php endif; ?>
                            <li class="breadcrumb-item active text-white" aria-current="page">Artikel</li>
                        </ol>
                    </nav>
                    
                    <h1 class="display-5 fw-bold mb-3"><?= htmlspecialchars($row['judul']) ?></h1>
                    
                    <div class="d-flex flex-wrap align-items-center gap-3 mb-4">
                        <?php if (!empty($category)): ?>
                            <span class="badge bg-light text-primary rounded-pill px-3 py-2">
                                <i class="bi bi-tag me-1"></i><?= htmlspecialchars($category) ?>
                            </span>
                        <?php endif; ?>
                        
                        <span class="text-white opacity-75">
                            <i class="bi bi-calendar3 me-1"></i><?= $formattedDate ?>
                        </span>
                        
                        <span class="text-white opacity-75">
                            <i class="bi bi-eye me-1"></i><?= $viewCount ?> kali dibaca
                        </span>
                    </div>
                    
                    <div class="d-flex flex-wrap gap-2">
                        <button class="btn btn-light btn-sm rounded-pill px-3" id="printBtn">
                            <i class="bi bi-printer me-1"></i>Cetak
                        </button>
                        
                        <div class="dropdown">
                            <button class="btn btn-light btn-sm rounded-pill px-3 dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-share me-1"></i>Bagikan
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item share-link" href="#" data-platform="whatsapp"><i class="bi bi-whatsapp me-2 text-success"></i>WhatsApp</a></li>
                                <li><a class="dropdown-item share-link" href="#" data-platform="facebook"><i class="bi bi-facebook me-2 text-primary"></i>Facebook</a></li>
                                <li><a class="dropdown-item share-link" href="#" data-platform="twitter"><i class="bi bi-twitter me-2 text-info"></i>Twitter</a></li>
                                <li><a class="dropdown-item share-link" href="#" data-platform="telegram"><i class="bi bi-telegram me-2 text-info"></i>Telegram</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="#" id="copyLinkBtn"><i class="bi bi-link-45deg me-2"></i>Salin Link</a></li>
                            </ul>
                        </div>
                        
                        <button class="btn btn-light btn-sm rounded-pill px-3" id="favoriteBtn" data-id="<?= $id ?>">
                            <i class="bi bi-bookmark me-1"></i><span id="favoriteText">Simpan</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="custom-shape-divider-bottom">
            <svg data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none">
                <path d="M321.39,56.44c58-10.79,114.16-30.13,172-41.86,82.39-16.72,168.19-17.73,250.45-.39C823.78,31,906.67,72,985.66,92.83c70.05,18.48,146.53,26.09,214.34,3V0H0V27.35A600.21,600.21,0,0,0,321.39,56.44Z" class="shape-fill"></path>
            </svg>
        </div>
    </div>

    <!-- Article Content -->
    <div class="container py-5">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <!-- Featured Image -->
                <?php if (!empty($row['gambar'])): ?>
                    <div class="featured-image mb-4 rounded-4 overflow-hidden shadow-sm">
                        <img src="<?= htmlspecialchars($row['gambar']) ?>" class="img-fluid w-100" alt="<?= htmlspecialchars($row['judul']) ?>">
                    </div>
                <?php endif; ?>
                
                <!-- Article Content -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body p-4 p-lg-5">
                        <div class="article-content">
                            <?= $row['konten'] ?>
                        </div>
                    </div>
                </div>
                
                <!-- Author Info (if available) -->
                <?php if (!empty($row['penulis'])): ?>
                <div class="card border-0 shadow-sm rounded-4 mb-4 bg-light">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center">
                            <div class="author-avatar me-3">
                                <i class="bi bi-person-circle display-6"></i>
                            </div>
                            <div>
                                <h5 class="mb-1">Ditulis oleh <?= htmlspecialchars($row['penulis']) ?></h5>
                                <p class="text-muted mb-0">Penulis</p>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Navigation -->
                <div class="d-flex justify-content-between my-4">
                    <a href="index.php" class="btn btn-outline-primary rounded-pill px-4">
                        <i class="bi bi-arrow-left me-2"></i>Kembali ke Daftar
                    </a>
                    
                    <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="admin/edit.php?id=<?= $id ?>" class="btn btn-outline-success rounded-pill px-4">
                        <i class="bi bi-pencil me-2"></i>Edit Materi
                    </a>
                    <?php endif; ?>
                </div>
                
                <!-- Related Materials -->
                <div class="related-materials mt-5">
                    <h4 class="mb-4 section-title">
                        <i class="bi bi-journals me-2"></i>
                        <?= !empty($category) ? "Materi $category Lainnya" : "Materi Terkait" ?>
                    </h4>
                    
                    <div class="row g-4">
                        <?php 
                        $hasRelated = false;
                        if ($relatedResult) {
                            while ($related = $relatedResult->fetchArray(SQLITE3_ASSOC)):
                                $hasRelated = true;
                                $relatedDate = new DateTime($related['created_at']);
                                $relatedFormattedDate = $relatedDate->format('d M Y');
                        ?>
                            <div class="col-md-4">
                                <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden transition-all hover-shadow">
                                    <?php if (!empty($related['gambar'])): ?>
                                        <div class="card-img-container" style="height: 150px; overflow: hidden;">
                                            <img src="<?= htmlspecialchars($related['gambar']) ?>" class="card-img-top h-100 object-fit-cover" alt="<?= htmlspecialchars($related['judul']) ?>">
                                        </div>
                                    <?php else: ?>
                                        <div class="card-img-container bg-light d-flex align-items-center justify-content-center" style="height: 150px;">
                                            <i class="bi bi-journal-text" style="font-size: 2rem;"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div class="card-body">
                                        <small class="text-muted d-block mb-2">
                                            <i class="bi bi-calendar-date me-1"></i><?= $relatedFormattedDate ?>
                                        </small>
                                        <h6 class="card-title"><?= htmlspecialchars($related['judul']) ?></h6>
                                        <a href="baca.php?id=<?= $related['id'] ?>" class="stretched-link"></a>
                                    </div>
                                </div>
                            </div>
                        <?php 
                            endwhile;
                        }
                        ?>
                        
                        <?php if (!$hasRelated): ?>
                            <div class="col-12">
                                <div class="alert alert-info border-0 shadow-sm rounded-4">
                                    <i class="bi bi-info-circle me-2"></i>
                                    Belum ada materi terkait yang tersedia.
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if (!$hasViewCount): ?>
                    <!-- Database Update Notice for Admin -->
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <div class="alert alert-warning mt-4 border-0 shadow-sm">
                            <h5><i class="bi bi-exclamation-triangle me-2"></i>Perhatian Admin</h5>
                            <p>Kolom <code>view_count</code> belum ada di tabel <code>materi</code>. Untuk mengaktifkan fitur penghitung tampilan, tambahkan kolom tersebut dengan perintah SQL berikut:</p>
                            <div class="bg-light p-3 rounded-3 mt-2 mb-2">
                                <code>ALTER TABLE materi ADD COLUMN view_count INTEGER DEFAULT 0;</code>
                            </div>
                            <p class="mb-0">Setelah menjalankan perintah tersebut, fitur penghitung tampilan akan berfungsi dengan baik.</p>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Toast Notification -->
<div class="toast-container position-fixed bottom-0 end-0 p-3">
    <div id="liveToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header">
            <i id="toastIcon" class="bi bi-info-circle me-2"></i>
            <strong class="me-auto" id="toastTitle">Notifikasi</strong>
            <small id="toastTime">Baru saja</small>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body" id="toastMessage">
            Pesan notifikasi akan muncul di sini.
        </div>
    </div>
</div>

<!-- Theme Toggle Button -->
<button id="themeToggleBtn" class="btn btn-light rounded-circle position-fixed bottom-0 start-0 m-4 border shadow-sm" style="width: 50px; height: 50px; z-index: 1000;">
    <i class="bi bi-moon"></i>
</button>

<?php
} catch (Exception $e) {
    echo '<div class="container py-5"><div class="alert alert-danger shadow-sm border-0 rounded-4 p-4">
            <div class="d-flex">
                <div class="me-3">
                    <i class="bi bi-exclamation-triangle-fill fs-1"></i>
                </div>
                <div>
                    <h4 class="alert-heading">Terjadi Kesalahan</h4>
                    <p class="mb-0">Error: ' . htmlspecialchars($e->getMessage()) . '</p>
                    <div class="mt-3">
                        <a href="index.php" class="btn btn-outline-danger">Kembali ke Beranda</a>
                    </div>
                </div>
            </div>
          </div></div>';
}

// Add required CSS libraries
echo '
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
';

// Add custom styles
echo '<style>
    :root {
        --primary-color: #4361ee;
        --secondary-color: #3f37c9;
        --success-color: #4cc9f0;
        --info-color: #4895ef;
        --warning-color: #f72585;
        --danger-color: #e63946;
        --light-color: #f8f9fa;
        --dark-color: #212529;
    }
    
    /* Theme Colors */
    .bg-gradient-primary {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    }
    
    body {
        transition: background-color 0.3s ease, color 0.3s ease;
    }
    
    /* Dark Mode Styles */
    body.dark-mode {
        background-color: #121212;
        color: #e0e0e0;
    }
    
    .dark-mode .card,
    .dark-mode .modal-content,
    .dark-mode .form-control,
    .dark-mode .form-select,
    .dark-mode .input-group-text {
        background-color: #1e1e1e;
        border-color: #333;
        color: #e0e0e0;
    }
    
    .dark-mode .card-title,
    .dark-mode .modal-title,
    .dark-mode h1, 
    .dark-mode h2, 
    .dark-mode h3, 
    .dark-mode h4, 
    .dark-mode h5, 
    .dark-mode h6 {
        color: #fff;
    }
    
    .dark-mode .text-muted,
    .dark-mode .form-text {
        color: #aaa !important;
    }
    
    .dark-mode .card-img-container.bg-light {
        background-color: #2a2a2a !important;
    }
    
    .dark-mode .btn-light {
        background-color: #333;
        border-color: #444;
        color: #e0e0e0;
    }
    
    .dark-mode .btn-outline-primary {
        border-color: var(--primary-color);
        color: var(--primary-color);
    }
    
    .dark-mode .btn-outline-success {
        border-color: var(--success-color);
        color: var(--success-color);
    }
    
    .dark-mode .dropdown-menu {
        background-color: #1e1e1e;
        border-color: #333;
    }
    
    .dark-mode .dropdown-item {
        color: #e0e0e0;
    }
    
    .dark-mode .dropdown-item:hover {
        background-color: #333;
    }
    
    .dark-mode .dropdown-divider {
        border-color: #444;
    }
    
    .dark-mode .alert-info {
        background-color: #1a3a4a;
        color: #9bd4ea;
        border-color: #164a63;
    }
    
    .dark-mode .alert-warning {
        background-color: #4a3a1a;
        color: #eac99b;
        border-color: #634a16;
    }
    
    .dark-mode .breadcrumb-item.active {
        color: #fff;
    }
    
    .dark-mode .breadcrumb-item + .breadcrumb-item::before {
        color: rgba(255, 255, 255, 0.5);
    }
    
    .dark-mode code {
        background-color: #2a2a2a;
        color: #e0e0e0;
    }
    
    .dark-mode .bg-light {
        background-color: #2a2a2a !important;
    }
    
    /* Article Header */
    .article-header {
        position: relative;
        padding: 3rem 0;
        margin-bottom: 2rem;
    }
    
    .custom-shape-divider-bottom {
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        overflow: hidden;
        line-height: 0;
    }
    
    .custom-shape-divider-bottom svg {
        position: relative;
        display: block;
        width: calc(100% + 1.3px);
        height: 70px;
    }
    
    .custom-shape-divider-bottom .shape-fill {
        fill: #FFFFFF;
    }
    
    .dark-mode .custom-shape-divider-bottom .shape-fill {
        fill: #121212;
    }
    
    /* Article Content */
    .article-content {
        font-size: 1.1rem;
        line-height: 1.8;
    }
    
    .article-content p {
        margin-bottom: 1.5rem;
    }
    
    .article-content h2 {
        margin-top: 2rem;
        margin-bottom: 1rem;
        font-weight: 700;
    }
    
    .article-content h3 {
        margin-top: 1.8rem;
        margin-bottom: 0.8rem;
        font-weight: 600;
    }
    
    .article-content img {
        max-width: 100%;
        height: auto;
        border-radius: 0.5rem;
        margin: 1.5rem 0;
    }
    
    .article-content ul, 
    .article-content ol {
        margin-bottom: 1.5rem;
        padding-left: 1.5rem;
    }
    
    .article-content li {
        margin-bottom: 0.5rem;
    }
    
    .article-content blockquote {
        border-left: 4px solid var(--primary-color);
        padding-left: 1rem;
        margin-left: 0;
        color: #6c757d;
        font-style: italic;
        margin-bottom: 1.5rem;
    }
    
    .article-content code {
        background-color: #f8f9fa;
        padding: 0.2rem 0.4rem;
        border-radius: 0.25rem;
        font-size: 0.875em;
    }
    
    .dark-mode .article-content code {
        background-color: #2a2a2a;
        color: #e0e0e0;
    }
    
    .article-content pre {
        background-color: #f8f9fa;
        padding: 1rem;
        border-radius: 0.5rem;
        overflow-x: auto;
        margin-bottom: 1.5rem;
    }
    
    .dark-mode .article-content pre {
        background-color: #2a2a2a;
        color: #e0e0e0;
    }
    
    .article-content table {
        width: 100%;
        margin-bottom: 1.5rem;
        border-collapse: collapse;
    }
    
    .article-content table th,
    .article-content table td {
        padding: 0.75rem;
        border: 1px solid #dee2e6;
    }
    
    .dark-mode .article-content table th,
    .dark-mode .article-content table td {
        border-color: #444;
    }
    
    .article-content table th {
        background-color: #f8f9fa;
        font-weight: 600;
    }
    
    .dark-mode .article-content table th {
        background-color: #2a2a2a;
    }
    
    /* Related Materials */
    .hover-shadow {
        transition: all 0.3s ease;
    }
    
    .hover-shadow:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
    }
    
    .object-fit-cover {
        object-fit: cover;
    }
    
    /* Section Title */
    .section-title {
        position: relative;
        padding-bottom: 0.5rem;
        margin-bottom: 1.5rem;
        font-weight: 700;
    }
    
    .section-title::after {
        content: "";
        position: absolute;
        left: 0;
        bottom: 0;
        height: 4px;
        width: 50px;
        background-color: var(--primary-color);
        border-radius: 2px;
    }
    
    /* Print Styles */
    @media print {
        .article-header, 
        .related-materials,
        #themeToggleBtn,
        .toast-container,
        footer,
        .btn,
        .alert-warning {
            display: none !important;
        }
        
        .container {
            width: 100% !important;
            max-width: 100% !important;
        }
        
        .article-content {
            font-size: 12pt;
        }
        
        .card {
            box-shadow: none !important;
            border: none !important;
        }
        
        .card-body {
            padding: 0 !important;
        }
    }
</style>';

require_once 'assets/template/footer.php';
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Store references to DOM elements
    const themeToggleBtn = document.getElementById('themeToggleBtn');
    const themeIcon = themeToggleBtn.querySelector('i');
    const favoriteBtn = document.getElementById('favoriteBtn');
    const favoriteText = document.getElementById('favoriteText');
    const printBtn = document.getElementById('printBtn');
    const copyLinkBtn = document.getElementById('copyLinkBtn');
    const shareLinks = document.querySelectorAll('.share-link');
    const appContainer = document.getElementById('app-container');
    
    // Initialize toast
    const toastEl = document.getElementById('liveToast');
    const toast = new bootstrap.Toast(toastEl);
    const toastTitle = document.getElementById('toastTitle');
    const toastMessage = document.getElementById('toastMessage');
    const toastIcon = document.getElementById('toastIcon');
    const toastTime = document.getElementById('toastTime');
    
    // Get material ID
    const materialId = favoriteBtn.getAttribute('data-id');
    
    // Initialize favorites from localStorage
    let favorites = JSON.parse(localStorage.getItem('favorites')) || [];
    
    // Update favorite button state
    updateFavoriteButton();
    
    // Apply saved theme preference
    if (localStorage.getItem('theme') === 'dark') {
        document.body.classList.add('dark-mode');
        appContainer.classList.add('dark-mode');
        themeIcon.classList.remove('bi-moon');
        themeIcon.classList.add('bi-sun');
    }
    
    // Theme toggle functionality
    themeToggleBtn.addEventListener('click', function() {
        document.body.classList.toggle('dark-mode');
        appContainer.classList.toggle('dark-mode');
        
        if (document.body.classList.contains('dark-mode')) {
            themeIcon.classList.remove('bi-moon');
            themeIcon.classList.add('bi-sun');
            localStorage.setItem('theme', 'dark');
        } else {
            themeIcon.classList.remove('bi-sun');
            themeIcon.classList.add('bi-moon');
            localStorage.setItem('theme', 'light');
        }
    });
    
    // Favorite button functionality
    favoriteBtn.addEventListener('click', function() {
        if (favorites.includes(materialId)) {
            // Remove from favorites
            favorites = favorites.filter(id => id !== materialId);
            showToast('Dihapus dari favorit', 'Materi telah dihapus dari daftar favorit Anda', 'warning');
        } else {
            // Add to favorites
            favorites.push(materialId);
            showToast('Ditambahkan ke favorit', 'Materi telah ditambahkan ke daftar favorit Anda', 'success');
        }
        
        // Save to localStorage
        localStorage.setItem('favorites', JSON.stringify(favorites));
        
        // Update UI
        updateFavoriteButton();
    });
    
    // Print functionality
    printBtn.addEventListener('click', function() {
        window.print();
    });
    
    // Copy link functionality
    copyLinkBtn.addEventListener('click', function(e) {
        e.preventDefault();
        
        // Create a temporary input element
        const tempInput = document.createElement('input');
        tempInput.value = window.location.href;
        document.body.appendChild(tempInput);
        
        // Select and copy the link
        tempInput.select();
        document.execCommand('copy');
        
        // Remove the temporary element
        document.body.removeChild(tempInput);
        
        // Show toast notification
        showToast('Link Disalin', 'Link artikel telah disalin ke clipboard', 'success');
    });
    
    // Share links functionality
    shareLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            const platform = this.getAttribute('data-platform');
            const url = encodeURIComponent(window.location.href);
            const title = encodeURIComponent(document.title);
            let shareUrl = '';
            
            switch (platform) {
                case 'whatsapp':
                    shareUrl = `https://api.whatsapp.com/send?text=${title}%20${url}`;
                    break;
                case 'facebook':
                    shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${url}`;
                    break;
                case 'twitter':
                    shareUrl = `https://twitter.com/intent/tweet?text=${title}&url=${url}`;
                    break;
                case 'telegram':
                    shareUrl = `https://t.me/share/url?url=${url}&text=${title}`;
                    break;
            }
            
            if (shareUrl) {
                window.open(shareUrl, '_blank', 'width=600,height=400');
            }
        });
    });
    
    // Functions
    function updateFavoriteButton() {
        if (favorites.includes(materialId)) {
            favoriteBtn.classList.remove('btn-light');
            favoriteBtn.classList.add('btn-warning');
            favoriteText.textContent = 'Tersimpan';
            favoriteBtn.querySelector('i').classList.remove('bi-bookmark');
            favoriteBtn.querySelector('i').classList.add('bi-bookmark-fill');
        } else {
            favoriteBtn.classList.remove('btn-warning');
            favoriteBtn.classList.add('btn-light');
            favoriteText.textContent = 'Simpan';
            favoriteBtn.querySelector('i').classList.remove('bi-bookmark-fill');
            favoriteBtn.querySelector('i').classList.add('bi-bookmark');
        }
    }
    
    function showToast(title, message, type = 'info') {
        toastTitle.textContent = title;
        toastMessage.textContent = message;
        toastTime.textContent = 'Baru saja';
        
        // Set icon based on type
        toastIcon.className = 'bi me-2';
        switch (type) {
            case 'success':
                toastIcon.classList.add('bi-check-circle-fill', 'text-success');
                break;
            case 'warning':
                toastIcon.classList.add('bi-exclamation-triangle-fill', 'text-warning');
                break;
            case 'danger':
                toastIcon.classList.add('bi-x-circle-fill', 'text-danger');
                break;
            default:
                toastIcon.classList.add('bi-info-circle-fill', 'text-info');
        }
        
        toast.show();
    }
    
    // Handle image loading
    const articleImages = document.querySelectorAll('.article-content img');
    articleImages.forEach(img => {
        // Add classes for styling
        img.classList.add('img-fluid', 'rounded-3', 'shadow-sm');
        
        // Add lightbox functionality if needed
        img.addEventListener('click', function() {
            // You could implement a lightbox here
        });
    });
});
</script>