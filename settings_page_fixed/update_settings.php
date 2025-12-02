<?php
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'inventory_system');
define('DB_USER', 'root'); 
define('DB_PASS', '');

try {

    $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

function get_user($user_id){
    global $db;
    $stmt = $db->prepare('SELECT account_ID, first_name, last_name, email, role, status FROM account WHERE account_ID = ?');
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    if ($user) {
        $user['avatar'] = null; 
    }
    
    return $user;
}

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    if(!isset($_SESSION['user_id'])){header('Location: login.php');exit;}
    $user_id = $_SESSION['user_id'];
    $action = $_POST['action'] ?? '';
    if($action === 'upload_avatar' && isset($_FILES['avatar']) && $_FILES['avatar']['error'] === 0){
        $f = $_FILES['avatar'];
        $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','webp'];
        
        if(in_array($ext, $allowed) && $f['size'] <= 2000000){
            $name = 'avatar_'.time().'_'.bin2hex(random_bytes(6)).'.'.$ext;
            if(!is_dir('uploads')) mkdir('uploads', 0755, true);
            move_uploaded_file($f['tmp_name'], 'uploads/'.$name);
            $stmt = $db->prepare('UPDATE account SET avatar = ? WHERE account_ID = ?');
            $stmt->execute([$name, $user_id]);
        }
    }

    if($action === 'update_profile'){
        $first = trim($_POST['first_name'] ?? '');
        $last = trim($_POST['last_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        
        if(filter_var($email, FILTER_VALIDATE_EMAIL) && $first !== '' && $last !== ''){
            $stmt = $db->prepare('UPDATE account SET first_name = ?, last_name = ?, email = ? WHERE account_ID = ?');
            $stmt->execute([$first, $last, $email, $user_id]);
        }
    }

    header('Location: settings.php');
    exit;
}