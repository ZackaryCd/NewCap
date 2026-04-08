<?php
header('Content-Type: application/json');
require '../Backend/db.php';

$folder_id = $_POST['folder_id']; // Ang ID ng sub-folder kung nasaan ang user
$uploader_id = 1; // Halimbawa lang, dapat galing sa $_SESSION

if (isset($_FILES['file'])) {
    $file = $_FILES['file'];
    $display_name = $file['name'];
    $file_size = $file['size'];
    $file_ext = pathinfo($display_name, PATHINFO_EXTENSION);
    
    $storage_name = uniqid() . "." . $file_ext;
    $upload_path = "../uploads/vault/" . $storage_name;

    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
        $stmt = $conn->prepare("INSERT INTO files (folder_id, uploader_id, display_name, storage_name, file_type, file_size) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iisssi", $folder_id, $uploader_id, $display_name, $storage_name, $file_ext, $file_size);
        $stmt->execute();
        
        logActivity($conn, $uploader_id, 'Upload File', $display_name, 'File');
        echo json_encode(['status' => 'success']);
    }
}