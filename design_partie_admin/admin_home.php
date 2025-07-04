<?php
require_once '../php_partie_admin/connection_admin.php';

if (!isset($_SESSION['admin_logged_in']) || !isset($_SESSION['admin_id'])) {
    header("Location: admin.php");
    exit();
}

// Connexion à la base de données
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Échec de la connexion : " . $e->getMessage());
}

// Récupérer les données de l'administrateur
$admin_stmt = $pdo->prepare("SELECT username, email, profile_image FROM admins WHERE admin_id = :admin_id");
$admin_stmt->execute([':admin_id' => $_SESSION['admin_id']]);
$admin = $admin_stmt->fetch(PDO::FETCH_ASSOC);
if ($admin === false) {
    session_unset();
    session_destroy();
    header("Location: admin.php");
    exit();
}
$admin_name = $admin['username'];
$admin_email = $admin['email'];
$admin_profile_image = $admin['profile_image'] ? "../design_partie_user/uploads/" . htmlspecialchars($admin['profile_image']) : "images/admin.jpg";

// Gérer la mise à jour du compte
if (isset($_POST['update_account'])) {
    $new_username = $_POST['username'] ?? $admin_name;
    $new_email = $_POST['email'] ?? $admin_email;
    $new_password = $_POST['password'] ?? '';
    $new_image = $admin_profile_image;

    // Gérer le téléchargement de l'image
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../design_partie_user/uploads/';
        $new_image = uniqid() . '_' . basename($_FILES['profile_image']['name']);
        $target_file = $upload_dir . $new_image;
        move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_file);
    }

    // Mettre à jour le mot de passe s'il est fourni
    $password_update = '';
    if (!empty($new_password)) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $password_update = ", password = :password";
    }

    $update_stmt = $pdo->prepare("UPDATE admins SET username = :username, email = :email, profile_image = :profile_image $password_update WHERE admin_id = :admin_id");
    $params = [
        ':username' => $new_username,
        ':email' => $new_email,
        ':profile_image' => basename($new_image),
        ':admin_id' => $_SESSION['admin_id']
    ];
    if (!empty($new_password)) {
        $params[':password'] = $hashed_password;
    }
    $success = $update_stmt->execute($params);

    // Retourner une réponse JSON
    header('Content-Type: application/json');
    echo json_encode(['success' => $success]);
    exit();
}

// Gérer la suppression du compte
if (isset($_POST['delete_account'])) {
    $delete_stmt = $pdo->prepare("DELETE FROM admins WHERE admin_id = :admin_id");
    $delete_stmt->execute([':admin_id' => $_SESSION['admin_id']]);
    session_unset();
    session_destroy();
    header("Location: admin.php");
    exit();
}

// Récupérer le nombre total d'utilisateurs
$total_users_stmt = $pdo->prepare("SELECT COUNT(*) as total FROM users");
$total_users_stmt->execute();
$total_users = $total_users_stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Récupérer le total des points
$total_points_stmt = $pdo->prepare("SELECT SUM(points) as total_points FROM users");
$total_points_stmt->execute();
$total_points = $total_points_stmt->fetch(PDO::FETCH_ASSOC)['total_points'] ?? 0;

// Récupérer les demandes de bacs avec le nom complet de l'utilisateur
$bin_requests_stmt = $pdo->prepare("SELECT br.request_id, u.full_name, br.country, br.state, br.city, br.location, br.notes, br.status, br.date_submitted 
                                   FROM bin_requests br 
                                   JOIN users u ON br.user_id = u.user_id 
                                   WHERE br.status = 'Pending'");
$bin_requests_stmt->execute();
$bin_requests = $bin_requests_stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les meilleurs utilisateurs avec image de profil
$top_users_stmt = $pdo->prepare("SELECT full_name, level, points, profile_image FROM users ORDER BY points DESC LIMIT 5");
$top_users_stmt->execute();
$top_users = $top_users_stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les emplacements de recyclage
$locations_stmt = $pdo->prepare("SELECT location_id, name, time, closing_time, location, image, country, state, city FROM recycling_locations");
$locations_stmt->execute();
$recycling_locations = $locations_stmt->fetchAll(PDO::FETCH_ASSOC);

// Gérer l'acceptation d'une demande de bac
if (isset($_POST['accept_request']) && isset($_POST['request_id'])) {
    $request_id = $_POST['request_id'];
    $location = $_POST['location'];
    $name = $_POST['name'];
    $time = $_POST['time'];
    $closing_time = $_POST['closing_time']; // Nouveau champ
    $country = $_POST['country'] ?? '';
    $state = $_POST['state'] ?? '';
    $city = $_POST['city'] ?? '';

    // Gérer le téléchargement de l'image
    $image = '';
    if (isset($_FILES['binImage']) && $_FILES['binImage']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../design_partie_user/uploads/';
        $image = uniqid() . '_' . basename($_FILES['binImage']['name']);
        $target_file = $upload_dir . $image;
        move_uploaded_file($_FILES['binImage']['tmp_name'], $target_file);
    }

    $insert_stmt = $pdo->prepare("INSERT INTO recycling_locations (name, location, time, closing_time, image, country, state, city) VALUES (:name, :location, :time, :closing_time, :image, :country, :state, :city)");
    $insert_stmt->execute([
        ':name' => $name,
        ':location' => $location,
        ':time' => $time,
        ':closing_time' => $closing_time,
        ':image' => $image,
        ':country' => $country,
        ':state' => $state,
        ':city' => $city
    ]);

    $update_stmt = $pdo->prepare("UPDATE bin_requests SET status = 'Accepted' WHERE request_id = :request_id");
    $update_stmt->execute([':request_id' => $request_id]);

    header("Location: admin_home.php");
    exit();
}

// Gérer le refus d'une demande de bac (suppression)
if (isset($_POST['decline_request']) && isset($_POST['request_id'])) {
    $request_id = $_POST['request_id'];
    $delete_stmt = $pdo->prepare("DELETE FROM bin_requests WHERE request_id = :request_id");
    $delete_stmt->execute([':request_id' => $request_id]);
    header("Location: admin_home.php");
    exit();
}

// Gérer la suppression d'un emplacement
if (isset($_POST['delete_location']) && isset($_POST['index'])) {
    $index = $_POST['index'];
    $delete_stmt = $pdo->prepare("DELETE FROM recycling_locations WHERE location_id = :id LIMIT 1");
    $delete_stmt->execute([':id' => $index]);
    header("Location: admin_home.php");
    exit();
}

// Gérer l'ajout d'un nouvel emplacement
if (isset($_POST['add_location'])) {
    $name = $_POST['location_name'];
    $location = $_POST['location_address'];
    $time = $_POST['location_time'];
    $closing_time = $_POST['location_closing_time']; // Nouveau champ
    $image = $_POST['location_image'] ?? ''; // Gérer le cas où l'image pourrait ne pas être définie
    $country = $_POST['location_country'];
    $state = $_POST['location_state'];
    $city = $_POST['location_city'];

    $insert_stmt = $pdo->prepare("INSERT INTO recycling_locations (name, location, time, closing_time, image, country, state, city) VALUES (:name, :location, :time, :closing_time, :image, :country, :state, :city)");
    $success = $insert_stmt->execute([
        ':name' => $name,
        ':location' => $location,
        ':time' => $time,
        ':closing_time' => $closing_time,
        ':image' => $image,
        ':country' => $country,
        ':state' => $state,
        ':city' => $city
    ]);

    // Retourner une réponse JSON
    header('Content-Type: application/json');
    echo json_encode(['success' => $success]);
    exit();
}

// Gérer l'ajout d'un nouveau défi avec date d'expiration
if (isset($_POST['add_challenge'])) {
    $action = $_POST['challenge_action'];
    $reward = $_POST['challenge_reward'];
    $goal = $_POST['challenge_goal']; // Objectif basé sur les points
    $expiration_date = date('Y-m-d H:i:s', strtotime($_POST['expiration_date'] . ' +2 days')); // Définir l'expiration 2 jours après la date sélectionnée
    $created_at = date('Y-m-d H:i:s');
    $updated_at = $created_at;

    $insert_stmt = $pdo->prepare("INSERT INTO admin_challenges (action, reward, goal, created_at, updated_at, expiration_date) VALUES (:action, :reward, :goal, :created_at, :updated_at, :expiration_date)");
    $insert_stmt->execute([
        ':action' => $action,
        ':reward' => $reward,
        ':goal' => $goal,
        ':created_at' => $created_at,
        ':updated_at' => $updated_at,
        ':expiration_date' => $expiration_date
    ]);

    header("Location: admin_home.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord Admin - CycleBins</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #0066cc;
            --primary-light: rgba(0, 102, 204, 0.1);
            --secondary: #004080;
            --accent: #ffab00;
            --dark: #263238;
            --light: #f5f5f5;
            --gray: #eceff1;
            --gradient: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', Arial, sans-serif;
            background-color: var(--light);
            color: var(--dark);
            overflow-x: hidden;
        }
        
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 2rem;
            background: var(--gradient);
            color: white;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .logo {
            font-size: 1.5rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .logo i {
            color: white;
        }
        
        .user-menu {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            position: relative;
            cursor: pointer;
        }
        
        .user-profile {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .user-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .user-name {
            font-weight: 600;
            transition: color 0.3s ease;
        }
        
        .user-menu:hover .user-name {
            color: var(--accent);
        }
        
        .user-dialog {
            display: none;
            position: fixed;
            top: 70px;
            right: 2rem;
            background: white;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
            padding: 1.5rem;
            width: 300px;
            z-index: 1001;
            animation: fadeIn 0.3s ease;
        }
        
        .user-dialog.active {
            display: block;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .user-dialog .profile-image {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            overflow: hidden;
            margin: 0 auto 1rem;
            border: 4px solid var(--primary-light);
        }
        
        .user-dialog .profile-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .user-dialog .user-info h3 {
            font-size: 1.2rem;
            color: var(--secondary);
            text-align: center;
            margin-bottom: 0.5rem;
        }
        
        .user-dialog .user-info p {
            font-size: 0.9rem;
            color: #666;
            text-align: center;
            margin-bottom: 1.5rem;
        }
        
        .user-dialog .dialog-buttons button {
            display: block;
            width: 100%;
            padding: 0.75rem;
            margin-bottom: 0.5rem;
            border: none;
            border-radius: 8px;
            background: var(--primary-light);
            color: var(--primary);
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
        }
        
        .user-dialog .dialog-buttons button:hover {
            background: var(--primary);
            color: white;
        }
        
        .user-dialog .dialog-buttons button:last-child {
            margin-bottom: 0;
        }
        
        .dashboard-container {
            display: flex;
            margin-top: 70px;
            min-height: calc(100vh - 70px);
        }
        
        .sidebar {
            background: white;
            border-right: 1px solid var(--gray);
            padding: 2rem 0;
            width: 250px;
            overflow-y: auto;
            position: fixed;
            height: calc(100vh - 70px);
            z-index: 900;
            top: 70px;
            left: 0;
        }
        
        .sidebar-menu {
            list-style: none;
        }
        
        .sidebar-menu li {
            margin-bottom: 0.5rem;
        }
        
        .sidebar-menu a {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.75rem 1.5rem;
            color: var(--dark);
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }
        
        .sidebar-menu a.active {
            background: var(--primary-light);
            border-left: 3px solid var(--primary);
            color: var(--primary);
        }
        
        .sidebar-menu i {
            width: 24px;
            text-align: center;
        }
        
        .main-content {
            padding: 2rem;
            flex: 1;
            margin-left: 250px;
            overflow-x: hidden;
        }

        
        .welcome-banner {
            background: var(--gradient);
            color: white;
            padding: 2rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
        }
        
        .welcome-banner::after {
            content: '';
            position: absolute;
            top: -50px;
            right: -50px;
            width: 200px;
            height: 200px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
        }
        
        .welcome-banner h2 {
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
        }
        
        .welcome-banner p {
            opacity: 0.9;
            max-width: 600px;
        }
        
        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        }
        
        .stat-card h3 {
            font-size: 1rem;
            color: #666;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .stat-card .value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary);
        }
        
        .dashboard-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
            width: 100%;
        }
        
        .dashboard-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            margin-bottom: 2rem;
            width: 100%;
        }
        
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        
        .card-header h3 {
            font-size: 1.25rem;
            color: var(--secondary);
        }
        
        .see-all {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.25rem;
            cursor: pointer;
        }
        
        .request-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }
        
        .request-table th,
        .request-table td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid var(--gray);
        }
        
        .request-table th {
            background-color: var(--primary-light);
            color: var(--primary);
            font-weight: 600;
        }
        
        .request-table td {
            background-color: var(--light);
        }
        
        .request-table tr:hover {
            background-color: white;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .user-list {
            list-style: none;
        }
        
        .user-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            background: var(--light);
            border-radius: 8px;
            margin-bottom: 0.5rem;
            transition: all 0.3s ease;
        }
        
        .user-item:hover {
            background: white;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .action-btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .accept-btn {
            background: #28a745;
            color: white;
            margin-right: 0.5rem;
        }
        
        .accept-btn:hover {
            background: #218838;
        }
        
        .decline-btn {
            background: #dc3545;
            color: white;
        }
        
        .decline-btn:hover {
            background: #c82333;
        }
        
        .add-location {
            padding: 1rem;
            background: var(--gradient);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
        }
        
        .add-location:hover {
            background: linear-gradient(135deg, var(--secondary) 0%, var(--primary) 100%);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 102, 204, 0.2);
        }
        
        .places-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            width: 100%;
        }
        
        .place-card {
            background: white;
            border-radius: 12px;
            padding: 1rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            text-align: center;
            cursor: pointer;
            position: relative;
        }
        
        .place-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        }
        
        .place-image {
            width: 100%;
            height: 150px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 1rem;
        }
        
        .place-name {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--secondary);
            margin-bottom: 0.5rem;
        }
        
        .place-time {
            font-size: 0.9rem;
            color: #666;
            margin-bottom: 0.5rem;
        }
        
        .place-location {
            font-size: 0.85rem;
            color: #666;
        }
        
        .place-location a {
            color: var(--primary);
            text-decoration: none;
        }
        
        .place-location a:hover {
            text-decoration: underline;
        }
        
        .delete-btn {
            position: absolute;
            top: 0.5rem;
            right: 0.5rem;
            background: #dc3545;
            color: white;
            border: none;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            cursor: pointer;
            font-size: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.3s ease;
        }
        
        .delete-btn:hover {
            background: #c82333;
        }
        
        .bin-form-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1002;
            justify-content: center;
            align-items: center;
        }
        
        .bin-form-modal.active {
            display: flex;
        }
        
        .bin-form {
            background: white;
            padding: 1rem;
            border-radius: 15px;
            width: 80%;
            max-width: 400px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
            animation: fadeIn 0.3s ease;
            position: relative;
            background: linear-gradient(135deg, #ffffff 0%, #f0f4f8 100%);
            border: 1px solid rgba(0, 102, 204, 0.2);
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            justify-content: space-between;
        }

        .bin-form h3 {
            color: var(--secondary);
            margin-bottom: 1rem;
            font-size: 1.5rem;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 1px;
            width: 100%;
        }
        
        .form-group {
            margin-bottom: 0.5rem;
            flex: 1 1 45%;
            min-width: 0;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.25rem;
            color: var(--dark);
            font-weight: 500;
            font-size: 0.9rem;
        }
        
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 0.5rem;
            border: 2px solid var(--gray);
            border-radius: 6px;
            font-size: 0.9rem;
            background: #ffffff;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }
        
        .form-group input[type="file"] {
            padding: 0.25rem 0;
        }
        
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 8px var(--primary-light);
        }

        .submit-btn {
            background: var(--primary);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
            font-weight: 600;
            text-transform: uppercase;
            margin-top: 0.5rem;
        }
        
        .submit-btn:hover {
            background: var(--secondary);
            transform: translateY(-2px);
        }
        
        .close-btn {
            position: absolute;
            top: 0.5rem;
            right: 0.5rem;
            background: none;
            border: none;
            font-size: 1.5rem;
            color: var(--dark);
            cursor: pointer;
            transition: color 0.3s ease;
        }
        
        .close-btn:hover {
            color: #f44336;
        }
        
        .edit-account-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1003;
            justify-content: center;
            align-items: center;
        }

        .edit-account-modal.active {
            display: flex;
        }

        .edit-form {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
            animation: fadeIn 0.3s ease;
            position: relative;
            background: linear-gradient(135deg, #ffffff 0%, #f0f4f8 100%);
            border: 1px solid rgba(0, 102, 204, 0.2);
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .edit-form .form-group {
            margin-bottom: 1rem;
        }

        .edit-form .form-group label {
            font-size: 1rem;
            display: block;
            margin-bottom: 0.5rem;
        }

        .edit-form .form-group input,
        .edit-form .form-group input[type="file"] {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid var(--gray);
            border-radius: 6px;
            font-size: 1rem;
            background: #ffffff;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        .edit-form .form-group input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 8px var(--primary-light);
        }

        .edit-form .submit-btn {
            background: var(--primary);
            color: white;
            border: none;
            padding: 0.75rem;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 600;
            text-transform: uppercase;
        }

        .edit-form .submit-btn:hover {
            background: var(--secondary);
            transform: translateY(-2px);
        }

        @media (max-width: 1200px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
            
            .main-content {
                padding: 1.5rem;
            }
        }
        
        @media (max-width: 768px) {
            .dashboard-container {
                flex-direction: column;
            }
            
            .sidebar {
                position: fixed;
                width: 250px;
                height: calc(100vh - 70px);
                top: 70px;
                left: 0;
                padding: 2rem 0;
            }
            
            .main-content {
                margin-left: 250px;
                width: calc(100% - 250px);
                padding: 1rem;
            }
            
            .sidebar-menu {
                display: flex;
                flex-direction: column;
                overflow-y: auto;
                padding-bottom: 0.5rem;
            }
            
            .sidebar-menu li {
                margin-bottom: 0;
                margin-right: 0;
            }
            
            .sidebar-menu a {
                padding: 0.5rem 1rem;
                border-left: none;
                border-bottom: 3px solid transparent;
            }
            
            .sidebar-menu a.active {
                border-left: none;
                border-bottom: 3px solid var(--primary);
            }
        }
        
        @media (max-width: 480px) {
            .stats-cards {
                grid-template-columns: 1fr;
            }
            
            .welcome-banner h2 {
                font-size: 1.5rem;
            }
            
            .card-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }
            
            .user-dialog {
                width: 250px;
                right: 1rem;
            }
            
            .sidebar {
                width: 200px;
            }
            
            .main-content {
                margin-left: 200px;
                width: calc(100% - 200px);
            }

            .bin-form {
                flex-direction: column;
                width: 90%;
            }
            
            .form-group {
                flex: 1 1 100%;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="logo">
            <i class="fas fa-recycle"></i>
            <span>CycleBins</span>
        </div>
        <div class="user-menu" onclick="toggleDialog()">
            <div class="user-profile">
                <div class="user-avatar"><img src="<?php echo $admin_profile_image; ?>" alt="Profil"></div>
                <span class="user-name"><?php echo htmlspecialchars($admin_name); ?></span>
            </div>
        </div>
    </nav>

    <div class="dashboard-container">
        <aside class="sidebar">
            <ul class="sidebar-menu">
                <li><a href="#tableau_de_bord" class="active"><i class="fas fa-tachometer-alt"></i> Tableau de bord</a></li>
                <li><a href="#demandes_bacs"><i class="fas fa-boxes"></i> Demandes de bacs</a></li>
                <li><a href="#meilleurs_utilisateurs"><i class="fas fa-users"></i> Meilleurs utilisateurs</a></li>
                <li><a href="#emplacements_recyclage"><i class="fas fa-map-marker-alt"></i> Emplacements de recyclage</a></li>
                <li><a href="#ajouter_emplacement"><i class="fas fa-plus"></i> Ajouter un emplacement</a></li>
                <li><a href="#ajouter_defi"><i class="fas fa-trophy"></i> Ajouter un défi</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <section id="tableau_de_bord">
                <div class="welcome-banner animate__animated animate__fadeIn">
                    <h2>Bienvenue, <?php echo htmlspecialchars($admin_name); ?> !</h2>
                    <p>Gérez les opérations de CycleBins, examinez les demandes et supervisez les meilleurs utilisateurs depuis ce tableau de bord.</p>
                </div>
                <div class="stats-cards">
                    <div class="stat-card animate__animated animate__fadeInUp">
                        <h3><i class="fas fa-users"></i> Total des utilisateurs</h3>
                        <div class="value"><?php echo htmlspecialchars($total_users); ?></div>
                    </div>
                    <div class="stat-card animate__animated animate__fadeInUp animate__delay-1s">
                        <h3><i class="fas fa-boxes"></i> Demandes de bacs</h3>
                        <div class="value"><?php echo count($bin_requests); ?></div>
                    </div>
                    <div class="stat-card animate__animated animate__fadeInUp animate__delay-2s">
                        <h3><i class="fas fa-map-marker-alt"></i> Emplacements</h3>
                        <div class="value"><?php echo count($recycling_locations); ?></div>
                    </div>
                    <div class="stat-card animate__animated animate__fadeInUp animate__delay-3s">
                        <h3><i class="fas fa-chart-line"></i> Total des points</h3>
                        <div class="value"><?php echo number_format($total_points); ?></div>
                    </div>
                </div>
            </section>

            <section id="demandes_bacs">
                <div class="dashboard-card animate__animated animate__fadeIn">
                    <div class="card-header">
                        <h3>Demandes de bacs</h3>
                    </div>
                    <table class="request-table">
                        <thead>
                            <tr>
                                <th>ID de la demande</th>
                                <th>Utilisateur</th>
                                <th>Emplacement</th>
                                <th>Date de soumission</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bin_requests as $request): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($request['request_id']); ?></td>
                                <td><?php echo htmlspecialchars($request['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($request['location']) . '<br>' . htmlspecialchars($request['country']) . ', ' . htmlspecialchars($request['state']) . ', ' . htmlspecialchars($request['city']); ?></td>
                                <td><?php echo htmlspecialchars($request['date_submitted']); ?></td>
                                <td>
                                    <button class="action-btn accept-btn" onclick="showBinForm('<?php echo htmlspecialchars($request['location']); ?>', '<?php echo htmlspecialchars($request['request_id']); ?>', '<?php echo htmlspecialchars($request['country']); ?>', '<?php echo htmlspecialchars($request['state']); ?>', '<?php echo htmlspecialchars($request['city']); ?>')">Accepter</button>
                                    <form method="post" style="display: inline;">
                                        <input type="hidden" name="decline_request" value="1">
                                        <input type="hidden" name="request_id" value="<?php echo htmlspecialchars($request['request_id']); ?>">
                                        <button type="submit" class="action-btn decline-btn">Refuser</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <section id="meilleurs_utilisateurs">
                <div class="dashboard-card animate__animated animate__fadeIn">
                    <div class="card-header">
                        <h3>Meilleurs utilisateurs</h3>
                        
                    </div>
                    <ul class="user-list">
                        <?php foreach ($top_users as $user): ?>
                        <li class="user-item">
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <div class="user-avatar" style="width: 30px; height: 30px;">
                                    <img src="<?php echo $user['profile_image'] ? "../design_partie_user/uploads/" . htmlspecialchars($user['profile_image']) : "images/default-user.jpg"; ?>" alt="Profil utilisateur" style="border-radius: 50%;">
                                </div>
                                <span><?php echo htmlspecialchars($user['full_name']); ?></span>
                            </div>
                            <span><?php echo number_format($user['points']); ?> pts</span>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </section>

            <section id="emplacements_recyclage">
                <div class="dashboard-card animate__animated animate__fadeIn">
                    <div class="card-header">
                        <h3>Emplacements de recyclage</h3>
                        <a href="#" class="see-all">Voir tout <i class="fas fa-chevron-right"></i></a>
                    </div>
                    <div class="places-grid" id="locationsGrid">
                        <?php foreach ($recycling_locations as $location): ?>
                        <div class="place-card" data-index="<?php echo htmlspecialchars($location['location_id']); ?>">
                            <img src="<?php echo $location['image'] ? "../design_partie_user/uploads/" . htmlspecialchars($location['image']) : "images/default-location.jpg"; ?>" alt="<?php echo htmlspecialchars($location['name']); ?>" class="place-image">
                            <div class="place-name"><?php echo htmlspecialchars($location['name']); ?></div>
                            <div class="place-time">Ouvert : <?php echo htmlspecialchars($location['time']); ?> - Fermé : <?php echo isset($location['closing_time']) ? htmlspecialchars($location['closing_time']) : 'N/A'; ?></div>
                            <div class="place-location"><a href="<?php echo htmlspecialchars($location['location']); ?>" target="_blank">Voir l'emplacement</a></div>
                            <form method="post" style="display: inline;">
                                <input type="hidden" name="delete_location" value="1">
                                <input type="hidden" name="index" value="<?php echo htmlspecialchars($location['location_id']); ?>">
                                <button type="submit" class="delete-btn">×</button>
                            </form>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>

            <section id="ajouter_emplacement">
                <div class="dashboard-card animate__animated animate__fadeIn">
                    <div class="card-header">
                        <h3>Ajouter un emplacement de recyclage</h3>
                    </div>
                    <button class="add-location" onclick="showAddLocationForm()"> <i class="fas fa-plus"></i> Ajouter un nouvel emplacement</button>
                </div>
            </section>

            <section id="ajouter_defi">
                <div class="dashboard-card animate__animated animate__fadeIn">
                    <div class="card-header">
                        <h3>Ajouter un nouveau défi</h3>
                    </div>
                    <form method="post" class="challenge-form">
                        <div class="form-group">
                            <label for="challenge_action">Action (ex. : Obtenir 100 points en 2 jours) :</label>
                            <input type="text" id="challenge_action" name="challenge_action" placeholder="ex. : Obtenir 100 points en 2 jours" required>
                        </div>
                        <div class="form-group">
                            <label for="challenge_reward">Récompense (points) :</label>
                            <input type="number" id="challenge_reward" name="challenge_reward" placeholder="ex. : 50" required>
                        </div>
                        <div class="form-group">
                            <label for="challenge_goal">Objectif (points) :</label>
                            <input type="number" id="challenge_goal" name="challenge_goal" placeholder="ex. : 100" required>
                        </div>
                        <div class="form-group">
                            <label for="expiration_date">Date d'expiration :</label>
                            <input type="date" id="expiration_date" name="expiration_date" required>
                        </div>
                        <button type="submit" name="add_challenge" class="add-location"> <i class="fas fa-plus"></i> Ajouter un défi</button>
                    </form>
                </div>
            </section>
        </main>
    </div>

    <div class="user-dialog" id="userDialog">
        <div class="profile-image"><img src="<?php echo $admin_profile_image; ?>" alt="Profil"></div>
        <div class="user-info">
            <h3><?php echo htmlspecialchars($admin_name); ?></h3>
            <p><?php echo htmlspecialchars($admin_email); ?></p>
        </div>
        <div class="dialog-buttons">
            <button onclick="window.location.href='admin.php'">Changer de compte</button>
            <button onclick="if(confirm('Êtes-vous sûr de vouloir supprimer votre compte ?')) { document.getElementById('deleteAccountForm').submit(); }">Supprimer le compte</button>
            <button onclick="window.location.href='admin.php'">Déconnexion</button>
            <button onclick="showEditAccountForm()">Modifier le compte</button>
        </div>
        <form id="deleteAccountForm" method="post" style="display: none;">
            <input type="hidden" name="delete_account" value="1">
        </form>
    </div>

    <div class="bin-form-modal" id="binFormModal">
        <div class="bin-form">
            <button class="close-btn" onclick="hideBinForm()">×</button>
            <h3>Ajouter un nouvel emplacement de recyclage</h3>
            <form id="binForm" method="post" enctype="multipart/form-data" onsubmit="submitBinForm(event)">
                <input type="hidden" id="requestId" name="request_id">
                <input type="hidden" name="accept_request" value="1">
                <div class="form-group">
                    <label for="binLocation">Emplacement (URL Google Maps) :</label>
                    <input type="url" id="binLocation" name="location" placeholder="https://maps.google.com/..." required readonly>
                </div>
                <div class="form-group">
                    <label for="binName">Nom :</label>
                    <input type="text" id="binName" name="name" placeholder="ex. : Bac de recyclage Rue du Parc" required>
                </div>
                <div class="form-group">
                    <label for="binImage">Image :</label>
                    <input type="file" id="binImage" name="binImage" accept="image/*" required>
                </div>
                <div class="form-group">
                    <label for="openTime">Heure d'ouverture :</label>
                    <select id="openTime" name="time" required>
                        <?php for ($h = 0; $h < 24; $h++): ?>
                            <option value="<?php echo sprintf("%02d:00", $h); ?>"><?php echo sprintf("%02d:00", $h); ?></option>
                            <option value="<?php echo sprintf("%02d:30", $h); ?>"><?php echo sprintf("%02d:30", $h); ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="closingTime">Heure de fermeture :</label>
                    <select id="closingTime" name="closing_time" required>
                        <?php for ($h = 0; $h < 24; $h++): ?>
                            <option value="<?php echo sprintf("%02d:00", $h); ?>"><?php echo sprintf("%02d:00", $h); ?></option>
                            <option value="<?php echo sprintf("%02d:30", $h); ?>"><?php echo sprintf("%02d:30", $h); ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="locationCountry">Pays :</label>
                    <input type="text" id="locationCountry" name="country" required>
                </div>
                <div class="form-group">
                    <label for="locationState">Région :</label>
                    <input type="text" id="locationState" name="state" required>
                </div>
                <div class="form-group">
                    <label for="locationCity">Ville :</label>
                    <input type="text" id="locationCity" name="city" required>
                </div>
                <button type="submit" class="submit-btn">Ajouter l'emplacement</button>
            </form>
        </div>
    </div>

    <div class="edit-account-modal" id="editAccountModal">
        <div class="edit-form">
            <button class="close-btn" onclick="hideEditAccountForm()">×</button>
            <h3>Modifier les détails du compte</h3>
            <form method="post" enctype="multipart/form-data" onsubmit="submitEditAccountForm(event)">
                <input type="hidden" name="update_account" value="1">
                <div class="form-group">
                    <label for="edit_username">Nom d'utilisateur :</label>
                    <input type="text" id="edit_username" name="username" value="<?php echo htmlspecialchars($admin_name); ?>" required>
                </div>
                <div class="form-group">
                    <label for="edit_email">Email :</label>
                    <input type="email" id="edit_email" name="email" value="<?php echo htmlspecialchars($admin_email); ?>" required>
                </div>
                <div class="form-group">
                    <label for="edit_password">Nouveau mot de passe (laisser vide pour conserver l'actuel) :</label>
                    <input type="password" id="edit_password" name="password">
                </div>
                <div class="form-group">
                    <label for="edit_profile_image">Image de profil :</label>
                    <input type="file" id="edit_profile_image" name="profile_image" accept="image/*">
                </div>
                <button type="submit" class="submit-btn">Mettre à jour le compte</button>
            </form>
        </div>
    </div>

   <script>
    function toggleDialog() {
        const dialog = document.getElementById('userDialog');
        dialog.classList.toggle('active');
    }

    document.addEventListener('click', function(event) {
        const dialog = document.getElementById('userDialog');
        const userMenu = document.querySelector('.user-menu');
        if (!userMenu.contains(event.target) && !dialog.contains(event.target)) {
            dialog.classList.remove('active');
        }
    });

    document.querySelectorAll('.sidebar-menu a').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            document.querySelectorAll('.sidebar-menu a').forEach(a => a.classList.remove('active'));
            this.classList.add('active');
            const targetId = this.getAttribute('href').substring(1);
            document.getElementById(targetId).scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    });

    window.addEventListener('scroll', function() {
        const sections = document.querySelectorAll('section');
        const sidebarLinks = document.querySelectorAll('.sidebar-menu a');

        sections.forEach(section => {
            const rect = section.getBoundingClientRect();
            if (rect.top >= 0 && rect.top < window.innerHeight / 2) {
                const id = section.getAttribute('id');
                sidebarLinks.forEach(link => {
                    link.classList.remove('active');
                    if (link.getAttribute('href').substring(1) === id) {
                        link.classList.add('active');
                    }
                });
            }
        });
    });

    const animateOnScroll = function() {
        const elements = document.querySelectorAll('.stat-card, .dashboard-card');
        elements.forEach(element => {
            const elementPosition = element.getBoundingClientRect().top;
            const screenPosition = window.innerHeight / 1.3;
            if (elementPosition < screenPosition) {
                element.classList.add('animate__fadeInUp');
            }
        });
    };
    window.addEventListener('scroll', animateOnScroll);
    window.addEventListener('load', animateOnScroll);

    function showBinForm(location, requestId, country, state, city) {
        const modal = document.getElementById('binFormModal');
        const binLocation = document.getElementById('binLocation');
        const requestIdInput = document.getElementById('requestId');
        const locationCountry = document.getElementById('locationCountry');
        const locationState = document.getElementById('locationState');
        const locationCity = document.getElementById('locationCity');
        binLocation.value = location;
        requestIdInput.value = requestId;
        locationCountry.value = country;
        locationState.value = state;
        locationCity.value = city;
        binLocation.setAttribute('readonly', true); // Garder en lecture seule pour l'acceptation de la demande
        modal.classList.add('active');
    }

    function hideBinForm() {
        const modal = document.getElementById('binFormModal');
        modal.classList.remove('active');
        document.getElementById('binForm').reset();
    }

    function submitBinForm(event) {
        event.preventDefault();
        const form = document.getElementById('binForm');
        const formData = new FormData(form);
        fetch('admin_home.php', { // Mettre à jour avec l'URL correcte du fichier PHP
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                hideBinForm(); // Masquer la modale
                location.reload(); // Recharger la page
            } else {
                alert('Échec de l\'ajout de l\'emplacement.');
            }
        })
        .catch(error => {
            console.error('Erreur :', error);
            alert('Emplacement ajouté avec succès');
        });
    }

    function showAddLocationForm() {
        const modal = document.getElementById('binFormModal');
        const binLocation = document.getElementById('binLocation');
        document.getElementById('binForm').reset();
        document.getElementById('requestId').value = '';
        binLocation.removeAttribute('readonly');
        modal.classList.add('active');
    }

    function deleteLocation(index) {
        if (confirm('Êtes-vous sûr de vouloir supprimer cet emplacement ?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.style.display = 'none';
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'index';
            input.value = index;
            const deleteInput = document.createElement('input');
            deleteInput.type = 'hidden';
            deleteInput.name = 'delete_location';
            deleteInput.value = '1';
            form.appendChild(input);
            form.appendChild(deleteInput);
            document.body.appendChild(form);
            form.submit();
        }
    }

    function showEditAccountForm() {
        const modal = document.getElementById('editAccountModal');
        modal.classList.add('active');
    }

    function hideEditAccountForm() {
        const modal = document.getElementById('editAccountModal');
        modal.classList.remove('active');
        document.querySelector('#editAccountModal form').reset();
    }

    function submitEditAccountForm(event) {
        event.preventDefault();
        const form = document.querySelector('#editAccountModal form');
        const formData = new FormData(form);
        fetch('admin_home.php', { // Mettre à jour avec l'URL correcte du fichier PHP
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                hideEditAccountForm();
                location.reload();
            } else {
                alert('Échec de la mise à jour du compte.');
            }
        })
        .catch(error => console.error('Erreur :', error));
    }
</script>
</body>
</html>