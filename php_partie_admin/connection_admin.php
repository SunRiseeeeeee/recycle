<?php
session_start();

// Database connection
$host = 'localhost';
$dbname = 'cyclebins_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Sign Up Function for Admins
function signupAdmin($username, $email, $password, $full_name, $profile_image) {
    global $pdo;
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    
    $file_name = null;
    if (!empty($_FILES['profile_image']['name'])) {
        $target_dir = "C:/Users/baade/Documents/GitHub/recycle/design_partie_user/uploads/";
        $target_file = $target_dir . basename($_FILES['profile_image']['name']);
        $file_name = basename($_FILES['profile_image']['name']);
        
        if (!move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_file)) {
            error_log("File upload failed for username: $username - Error: " . $_FILES['profile_image']['error']);
            return false;
        }
    }

    $stmt = $pdo->prepare("INSERT INTO admins (username, email, password_hash, full_name, profile_image) VALUES (:username, :email, :password_hash, :full_name, :profile_image)");
    return $stmt->execute([
        ':username' => $username,
        ':email' => $email,
        ':password_hash' => $password_hash,
        ':full_name' => $full_name,
        ':profile_image' => $file_name
    ]);
}


// Sign In Function for Admins
function signinAdmin($username, $password) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = :username");
    $stmt->execute([':username' => $username]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($admin && password_verify($password, $admin['password_hash'])) {
        $_SESSION['admin_id'] = $admin['admin_id'];
        $_SESSION['username'] = $admin['username'];
        $_SESSION['admin_logged_in'] = true;
        return true;
    }
    return false;
}

// Check if admin is logged in
function isAdminLoggedIn() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

// Logout Function
function logoutAdmin() {
    session_unset();
    session_destroy();
}
?>
