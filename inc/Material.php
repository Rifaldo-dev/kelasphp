
<?php
class Material {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    public function getAllMaterials($search = '', $limit = 6, $offset = 0) {
        $search = '%' . $search . '%';
        $stmt = $this->db->prepare("SELECT * FROM materi WHERE judul LIKE ? ORDER BY created_at DESC LIMIT ? OFFSET ?");
        $stmt->bindValue(1, $search, SQLITE3_TEXT);
        $stmt->bindValue(2, $limit, SQLITE3_INTEGER);
        $stmt->bindValue(3, $offset, SQLITE3_INTEGER);
        return $stmt->execute();
    }
    
    public function getTotalCount($search = '') {
        $search = '%' . $search . '%';
        $result = $this->db->query("SELECT COUNT(*) as total FROM materi WHERE judul LIKE '$search'");
        return $result->fetchArray(SQLITE3_ASSOC)['total'];
    }
    
    public function formatDate($dateString) {
        if (empty($dateString)) return 'Tanggal tidak tersedia';
        $date = new DateTime($dateString);
        return $date->format('d M Y H:i');
    }
}
?>
