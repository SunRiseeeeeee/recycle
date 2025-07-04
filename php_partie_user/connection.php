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

// Sign Up Function for Users
function signupUser($username, $email, $password, $full_name, $profile_image = null) {
    global $pdo;
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("
        INSERT INTO users (username, email, password_hash, full_name, profile_image) 
        VALUES (:username, :email, :password_hash, :full_name, :profile_image)
    ");
    return $stmt->execute([
        ':username' => $username,
        ':email' => $email,
        ':password_hash' => $password_hash,
        ':full_name' => $full_name,
        ':profile_image' => $profile_image
    ]);
}


// Sign In Function for Users
function signinUser($username, $password) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['logged_in'] = true;
        return true;
    }
    return false;
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

// Logout Function
function logoutUser() {
    session_unset();
    session_destroy();
}
?>