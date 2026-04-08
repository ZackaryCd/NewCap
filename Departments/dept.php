<?php
header('Content-Type: application/json');
include '../Backend/db.php'; 

// Idinagdag ang has_passcode, has_rfid, at has_fingerprint sa SELECT clause
$sql = "SELECT 
            u.id, u.first_name, u.last_name, u.email, u.role, u.profile_picture, u.dept_id,
            u.has_passcode, u.has_rfid, u.has_fingerprint,
            d.name AS department_name,
            COUNT(f.id) AS file_count,
            IFNULL(SUM(f.file_size), 0) AS storage_bytes 
        FROM users u
        JOIN departments d ON u.dept_id = d.id
        LEFT JOIN files f ON u.id = f.uploader_id 
        WHERE u.status = 'Active' 
        GROUP BY u.id";

$result = $conn->query($sql);
$staffList = [];

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) { 
        // Siguraduhin na integer ang balik ng flags para sa tamang comparison sa JS
        $row['has_passcode'] = (int)$row['has_passcode'];
        $row['has_rfid'] = (int)$row['has_rfid'];
        $row['has_fingerprint'] = (int)$row['has_fingerprint'];
        $staffList[] = $row; 
    }
}

echo json_encode($staffList);
$conn->close();
?>