
<?php
require_once '../inc/config.php';
require_once '../inc/auth.php';

if ($_FILES['file']['error'] === 0) {
    $upload_dir = '../db/upload/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $file_name = time() . '_' . $_FILES['file']['name'];
    $target_file = $upload_dir . $file_name;
    
    if (move_uploaded_file($_FILES['file']['tmp_name'], $target_file)) {
        echo json_encode([
            'location' => '/db/upload/' . $file_name
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to upload file']);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'No file uploaded']);
}
