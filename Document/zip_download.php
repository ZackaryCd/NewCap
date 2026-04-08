<?php
require '../Backend/db.php'; // Gagamit ng port 3307

if (isset($_POST['file_ids'])) {
    $zip = new ZipArchive();
    $zipName = "Eufile_Batch_" . time() . ".zip";
    $zipPath = "../uploads/temp/" . $zipName;

    if ($zip->open($zipPath, ZipArchive::CREATE) === TRUE) {
        $ids = explode(',', $_POST['file_ids']);
        foreach ($ids as $id) {
            $stmt = $conn->prepare("SELECT storage_name, display_name FROM files WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $file = $stmt->get_result()->fetch_assoc();
            
            if ($file) {
                $zip->addFile("../uploads/vault/" . $file['storage_name'], $file['display_name']);
            }
        }
        $zip->close();

        echo json_encode(['status' => 'success', 'download_url' => 'uploads/temp/' . $zipName]);
    }
}
?>