<?php
header('Content-Type: application/json');
require '../Backend/db.php'; 

$parent_id = (isset($_GET['parent_id']) && $_GET['parent_id'] !== 'null' && $_GET['parent_id'] !== '') ? intval($_GET['parent_id']) : null;
$dept_id = (isset($_GET['dept_id']) && $_GET['dept_id'] !== 'null' && $_GET['dept_id'] !== '') ? intval($_GET['dept_id']) : null;
$search = (isset($_GET['search']) && $_GET['search'] !== '') ? "%" . $_GET['search'] . "%" : null;

$response = ['folders' => [], 'files' => []];

try {
    if ($parent_id === null && $dept_id === null && !$search) {
        $stmt = $conn->prepare("SELECT id, name, 'folder' AS type FROM departments");
        $stmt->execute();
        $response['folders'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        $stmt_files = $conn->prepare("SELECT id, display_name AS name, file_type AS type, file_size AS size, uploaded_at AS date, storage_name 
                                     FROM files WHERE folder_id IS NULL AND dept_id IS NULL AND is_trash = 0 AND is_archived = 0");
        $stmt_files->execute();
        $response['files'] = $stmt_files->get_result()->fetch_all(MYSQLI_ASSOC);
    } 
    else {
        $query_folder = "SELECT id, name, 'folder' AS type FROM folders WHERE parent_id <=> ? ";
        if ($dept_id !== null) $query_folder .= " AND dept_id = $dept_id ";
        $query_folder .= " AND is_trash = 0 AND is_archived = 0";
        if ($search) $query_folder .= " AND name LIKE ?";

        $stmt_f = $conn->prepare($query_folder);
        if ($search) $stmt_f->bind_param("is", $parent_id, $search);
        else $stmt_f->bind_param("i", $parent_id);
        $stmt_f->execute();
        $response['folders'] = $stmt_f->get_result()->fetch_all(MYSQLI_ASSOC);

        $query_file = "SELECT id, display_name AS name, file_type AS type, file_size AS size, uploaded_at AS date, storage_name 
                       FROM files WHERE folder_id <=> ? ";
        if ($dept_id !== null) $query_file .= " AND dept_id = $dept_id ";
        $query_file .= " AND is_trash = 0 AND is_archived = 0";
        if ($search) $query_file .= " AND display_name LIKE ?";

        $stmt_fi = $conn->prepare($query_file);
        if ($search) $stmt_fi->bind_param("is", $parent_id, $search);
        else $stmt_fi->bind_param("i", $parent_id);
        $stmt_fi->execute();
        $response['files'] = $stmt_fi->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    echo json_encode($response);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>