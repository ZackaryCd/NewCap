    <?php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL); 
    session_start();
    header('Content-Type: application/json');
    require 'db.php'; // Gagamit ng iyong $conn na may port 3307

    $data = json_decode(file_get_contents('php://input'), true);
    $email = $data['email'] ?? '';
    $password = $data['password'] ?? '';

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result(); // Mahalaga ito para sa mysqli
    $user = $res->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['dept_id'] = $user['dept_id'];
        
        // Tawagin ang logActivity function mula sa iyong db.php
        logActivity($conn, $user['id'], 'Login', $email, 'Session');
        
        echo json_encode(['status' => 'success', 'msg' => 'Welcome back!']);
    } else {
        echo json_encode(['status' => 'error', 'msg' => 'Invalid email or password.']);
    }

    $stmt->close();
    $conn->close();
    ?>