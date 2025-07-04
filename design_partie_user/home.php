<?php
require_once '../php_partie_user/connection.php';

if (isLoggedIn()) {
    $user_id = $_SESSION['user_id'];

    // Récupérer les données de l'utilisateur y compris profile_image
    $stmt = $pdo->prepare("SELECT username, email, points, level, profile_image FROM users WHERE user_id = :user_id");
    $stmt->execute([':user_id' => $user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $user_name = $user['username'] ?? 'Invité';
    $user_email = $user['email'] ?? '';
    $user_points = $user['points'] ?? 0;
    // Déterminer le niveau en fonction des points
    $user_level = $user['level'] ?? 'Débutant Éco';
    if ($user_points >= 1000) {
        $user_level = 'Champion Éco';
    } elseif ($user_points >= 300) {
        $user_level = 'Maître Éco';
    } elseif ($user_points >= 100) {
        $user_level = 'Guerrier Éco';
    }
    $user_profile_image = $user['profile_image'] ?? 'images/person.jpg'; // Image par défaut si null

    // Récupérer les activités récentes
    $stmt = $pdo->prepare("SELECT action, points, date FROM activity_log WHERE user_id = :user_id ORDER BY date DESC LIMIT 7");
    $stmt->execute([':user_id' => $user_id]);
    $recent_activity = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculer le total des objets recyclés par type
    $items_recycled_by_type = [];
    try {
        $stmt = $pdo->prepare("SELECT action FROM activity_log WHERE user_id = :user_id");
        $stmt->execute([':user_id' => $user_id]);
        $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($activities as $activity) {
            if (preg_match('/Recyclé\s+(.+?)\s+\((\d+\.?\d*)\s*kg\)/', $activity['action'], $matches)) {
                $recycle_type = $matches[1];
                $quantity = floatval($matches[2]);
                $items_recycled_by_type[$recycle_type] = ($items_recycled_by_type[$recycle_type] ?? 0) + $quantity;
            }
        }
    } catch (PDOException $e) {
        error_log("Erreur de base de données lors du calcul des objets recyclés : " . $e->getMessage());
    }
    $items_recycled = array_sum($items_recycled_by_type);

    // Récupérer les lieux à proximité
    $nearby_locations = $pdo->query("SELECT name, time, `location`, image, country, state, city, closing_time FROM recycling_locations ORDER BY RAND() LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);

    // Récupérer les défis depuis admin_challenges avec le progrès de l'utilisateur, en excluant les défis expirés
    $challenges = [];
    try {
        $stmt = $pdo->prepare("
            SELECT ac.challenge_id, ac.action, ac.reward, ac.goal, COALESCE(uc.progress, 0) AS progress, uc.completed_at, uc.badge_earned, ac.expiration_date
            FROM admin_challenges ac
            LEFT JOIN user_challenges uc ON ac.challenge_id = uc.challenge_id AND uc.user_id = :user_id
            WHERE ac.expiration_date > NOW()
            ORDER BY ac.goal ASC
        ");
        $stmt->execute([':user_id' => $user_id]);
        $challenges = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (empty($challenges)) {
            error_log("Aucun défi actif trouvé pour user_id : $user_id");
        } else {
            error_log("Défis récupérés : " . print_r($challenges, true));
        }
    } catch (PDOException $e) {
        error_log("Erreur de base de données lors de la récupération des défis : " . $e->getMessage());
    }

    // Récupérer le classement avec profile_image
    $leaderboard = $pdo->query("SELECT username, level, points, profile_image FROM users ORDER BY points DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);

    // Définir les taux de recyclage
    $recycling_rates = [
        "Plastique" => 10,
        "Papier" => 5,
        "Verre" => 8,
        "Aluminium" => 12,
        "Carton" => 6,
        "Déchets électroniques" => 15,
        "Textiles" => 4
    ];

    // Définir les pays, états et villes
    $countries_states_cities = [
        "Maroc" => [
            "Casablanca Settat" => ["Casablanca", "Mohammedia", "Settat", "Berrechid", "El Jadida"],
            "Marrakech Safi" => ["Marrakech", "Safi", "Essaouira", "Youssoufia", "Kelaa des Sraghna"],
            "Fès Meknès" => ["Fès", "Meknès", "Taza", "Sefrou", "Ifrane"],
            "Tanger Tétouan Al Hoceima" => ["Tanger", "Tétouan", "Al Hoceima", "Larache", "Chefchaouen"],
            "Rabat Salé Kénitra" => ["Rabat", "Salé", "Kénitra", "Skhirat-Témara", "Sidi Kacem"],
            "Béni Mellal Khénifra" => ["Béni Mellal", "Khénifra", "Azilal"],
            "Drâa Tafilalet" => ["Errachidia", "Ouarzazate", "Zagora"],
            "Souss Massa" => ["Agadir", "Inezgane", "Tiznit"],
            "Guelmim Oued Noun" => ["Guelmim", "Tan Tan", "Sidi Ifni"],
            "Laâyoune Sakia El Hamra" => ["Laayoune", "Boujdour", "Tarfaya"],
            "Dakhla Oued Ed Dahab" => ["Dakhla", "Oued Ed Dahab", "Bir Gandouin"]
        ],
        "Algérie" => [
            "Alger" => ["Alger", "Birkhadem", "Bab El Oued", "Hussein Dey", "Kouba"],
            "Oran" => ["Oran", "Arzew", "Bir El Djir", "Aïn El Turk", "Es Senia"],
            "Constantine" => ["Constantine", "Hamma Bouziane", "El Khroub", "Zighoud Youcef", "Aïn Smara"],
            "Annaba" => ["Annaba", "El Bouni", "Seraidi", "El Hadjar", "Berrahal"],
            "Tizi Ouzou" => ["Tizi Ouzou", "Azazga", "Boghni", "Draâ Ben Khedda", "Mekla"],
            "Batna" => ["Batna", "Merouana", "Timgad", "Aïn Touta"],
            "Béjaïa" => ["Béjaïa", "Amizour", "Kherrata", "Sidi Aïch"],
            "Biskra" => ["Biskra", "Sidi Okba", "El Kantara"],
            "Blida" => ["Blida", "Boufarik", "Bouïnane", "Oued El Alleug"],
            "Oran-étendu" => ["Saïda", "Mascara", "Tlemcen", "Sidi Bel Abbès", "Mostaganem"],
            "Sétif" => ["Sétif", "El Eulma", "Aïn Oulmene"],
            "Djelfa" => ["Djelfa", "Aïn Oussera", "Messâad"],
            "Ghardaïa" => ["Ghardaïa", "Metlili", "El Menea"],
            "Tamanrasset" => ["Tamanrasset", "In Salah", "In Guezzam"]
        ],
        "Tunisie" => [
            "Tunis" => ["Tunis", "La Goulette", "Carthage", "Sidi Bou Said", "Le Bardo"],
            "Ariana" => ["Ariana", "Raoued", "Mnihla", "Ettadhamen"],
            "Ben Arous" => ["Ben Arous", "El Mourouj", "Rades", "Hammam Lif"],
            "Manouba" => ["Manouba", "Douar Hicher", "Mornag", "Tebourba"],
            "Bizerte" => ["Bizerte", "Menzel Bourguiba", "Mateur", "Sejnane"],
            "Nabeul" => ["Nabeul", "Hammamet", "Kélibia", "Dar Chaabane", "Grombalia"],
            "Béja" => ["Béja", "Testour", "Nefza", "Téboursouk"],
            "Jendouba" => ["Jendouba", "Tabarka", "Ghardimaou", "Aïn Draham"],
            "Zaghouan" => ["Zaghouan", "Zriba", "Bir Mcherga"],
            "Siliana" => ["Siliana", "Gaâfour", "Bou Arada", "Makthar"],
            "Le Kef" => ["Kef", "Tajerouine", "Nebeur", "Sakiet Sidi Youssef"],
            "Sousse" => ["Sousse", "Msaken", "Akouda", "Hergla", "Enfidha"],
            "Monastir" => ["Monastir", "Moknine", "Bembla", "Jemmal"],
            "Mahdia" => ["Mahdia", "Ksour Essef", "Chebba", "Bou Merdes"],
            "Kasserine" => ["Kasserine", "Sbeitla", "Fériana", "Thala"],
            "Sidi Bouzid" => ["Sidi Bouzid", "Menzel Bouzaiane", "Regueb"],
            "Kairouan" => ["Kairouan", "Haffouz", "Chebika", "Oueslatia"],
            "Gafsa" => ["Gafsa", "El Ksar", "Métlaoui", "Redeyef"],
            "Sfax" => ["Sfax", "Sakiet Eddaier", "Sakiet Ezzit", "El Hencha"],
            "Gabès" => ["Gabès", "Matmata", "Mareth", "El Hamma"],
            "Médenine" => ["Médenine", "Ben Gardane", "Zarzis", "Houmt Souk"],
            "Tozeur" => ["Tozeur", "Nefta", "Degache"],
            "Kébili" => ["Kébili", "Douz", "Souk Lahad"],
            "Tataouine" => ["Tataouine", "Remada", "Ghomrassen", "Bir Lahmar"]
        ],
        "Libye" => [
            "Tripoli" => ["Tripoli", "Jafara", "Zawiya", "Tajura", "Al Maya"],
            "Benghazi" => ["Benghazi", "Butnan", "Derna", "Al Marj", "Tocra"],
            "Misrata" => ["Misrata", "Marj", "Zlitan", "Bani Walid", "Tawergha"],
            "Sirte" => ["Sirte", "Abu Hadi", "Harawa"],
            "Sabha" => ["Sabha", "Murzuq", "Ubari", "Brak", "Ghat"],
            "Al Jabal al Akhdar" => ["Al Bayda", "Shahhat", "Qubbah"],
            "Al Jabal al Gharbi" => ["Gharyan", "Yafran", "Al Qalaa"],
            "Al Kufra" => ["Kufra", "Tazirbu", "Al Jawf"],
            "An Nuqat al Khams" => ["Zuwara", "Al Ajaylat", "Al Jumayl"],
            "Al Wahat" => ["Ajdabiya", "Jalu", "Awjila"]
        ],
        "Mauritanie" => [
            "Adrar" => ["Atar", "Chinguetti", "Ouadane"],
            "Assaba" => ["Kiffa", "Barkewol", "Guerou"],
            "Brakna" => ["Aleg", "Boghé", "Bababé"],
            "Dakhlet Nouadhibou" => ["Nouadhibou", "Chami", "Boulenoir"],
            "Gorgol" => ["Kaédi", "Maghama", "M'Bout"],
            "Guidimaka" => ["Sélibaby", "Ould Yengé", "Bouanze"],
            "Hodh Ech Chargui" => ["Néma", "Oualata", "Timbedra"],
            "Hodh El Gharbi" => ["Ayoun el Atrous", "Touil", "Koubenni"],
            "Inchiri" => ["Akjoujt", "Aoujeft", "Benichab"],
            "Nouakchott Nord" => ["Dar Naim", "Teyarett", "Toujounine"],
            "Nouakchott Ouest" => ["Tevragh Zeina", "Ksar", "Sebkha"],
            "Nouakchott Sud" => ["Arafat", "El Mina", "Riyad"],
            "Tagant" => ["Tidjikja", "Moudjeria", "Tichitt"],
            "Tiris Zemmour" => ["Zouérat", "Fderick", "Bir Moghrein"],
            "Trarza" => ["Rosso", "Boutilimit", "Mederdra"]
        ]
    ];

    // Définir les seuils et noms des badges
    $badges = [
        ['name' => 'Débutant Vert', 'threshold' => 10, 'icon' => 'fas fa-seedling', 'earned' => false],
        ['name' => 'Héros du Plastique', 'threshold' => 50, 'icon' => 'fas fa-medal', 'earned' => false],
        ['name' => 'Champion Éco', 'threshold' => 1000, 'icon' => 'fas fa-award', 'earned' => false],
        ['name' => 'Recycleur Mensuel', 'threshold' => 100, 'icon' => 'fas fa-star', 'earned' => false]
    ];

    // Vérifier les badges gagnés en fonction des points
    foreach ($badges as &$badge) {
        if ($user_points >= $badge['threshold']) {
            $badge['earned'] = true;
        }
    }
    unset($badge);

    // Gérer la soumission de recyclage
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['recycle_submit'])) {
        $location_name = $_POST['location_name'];
        $recycle_type = $_POST['recycle_type'];
        $quantity = floatval($_POST['quantity']);
        $points = round($quantity * $recycling_rates[$recycle_type]);

        $stmt = $pdo->prepare("INSERT INTO activity_log (user_id, action, points, date) VALUES (:user_id, :action, :points, NOW())");
        $stmt->execute([
            ':user_id' => $user_id,
            ':action' => "Recyclé $recycle_type ($quantity kg) à $location_name",
            ':points' => $points
        ]);

        $stmt = $pdo->prepare("UPDATE users SET points = points + :points WHERE user_id = :user_id");
        $stmt->execute([':points' => $points, ':user_id' => $user_id]);

        // Mettre à jour le progrès des défis en fonction des points gagnés
        foreach ($challenges as $challenge) {
            $current_progress = $challenge['progress'];
            $new_progress = $current_progress + $points;

            $stmt = $pdo->prepare("
                INSERT INTO user_challenges (user_id, challenge_id, progress)
                VALUES (:user_id, :challenge_id, :progress)
                ON DUPLICATE KEY UPDATE progress = VALUES(progress)
            ");
            $success = $stmt->execute([
                ':user_id' => $user_id,
                ':challenge_id' => $challenge['challenge_id'],
                ':progress' => $new_progress
            ]);
            if (!$success) {
                error_log("Échec de la mise à jour du progrès pour challenge_id : " . $challenge['challenge_id'] . " - Erreur : " . print_r($stmt->errorInfo(), true));
            }

            if ($new_progress >= $challenge['goal'] && !$challenge['completed_at']) {
                $badge = 'Champion Éco'; // Attribuer un badge en fonction de la complétion du défi (personnalisable selon besoin)
                $stmt = $pdo->prepare("
                    UPDATE user_challenges 
                    SET progress = :progress, completed_at = NOW(), badge_earned = :badge
                    WHERE user_id = :user_id AND challenge_id = :challenge_id
                ");
                $stmt->execute([
                    ':progress' => $new_progress,
                    ':badge' => $badge,
                    ':user_id' => $user_id,
                    ':challenge_id' => $challenge['challenge_id']
                ]);

                $stmt = $pdo->prepare("UPDATE users SET points = points + :reward WHERE user_id = :user_id");
                $stmt->execute([':reward' => $challenge['reward'], ':user_id' => $user_id]);
            }
        }

        header("Location: home.php");
        exit();
    }

    // Gérer la soumission de demande de poubelle
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bin_request_submit'])) {
        $country = $_POST['country'];
        $state = $_POST['state'];
        $city = $_POST['city'];
        $location = $_POST['location'];
        $notes = $_POST['notes'];

        $stmt = $pdo->prepare("INSERT INTO bin_requests (user_id, country, state, city, location, notes, status, date_submitted) VALUES (:user_id, :country, :state, :city, :location, :notes, 'En attente', NOW())");
        $stmt->execute([
            ':user_id' => $user_id,
            ':country' => $country,
            ':state' => $state,
            ':city' => $city,
            ':location' => $location,
            ':notes' => $notes
        ]);

        header("Location: home.php");
        exit();
    }

    // Gérer la mise à jour du profil
    if (isset($_POST['update_profile'])) {
        $new_username = $_POST['username'] ?? $user_name;
        $new_email = $_POST['email'] ?? $user_email;
        $new_password = $_POST['password'] ?? '';
        $new_image = $user_profile_image;

        // Gérer le téléchargement d'image
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = 'uploads/';
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

        $update_stmt = $pdo->prepare("UPDATE users SET username = :username, email = :email, profile_image = :profile_image, level = :level $password_update WHERE user_id = :user_id");
        $params = [
            ':username' => $new_username,
            ':email' => $new_email,
            ':profile_image' => basename($new_image),
            ':level' => $user_level, // Mettre à jour le niveau dans la base de données
            ':user_id' => $user_id
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
} else {
    header("Location: signin.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord CycleBins</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #00c853;
            --primary-light: rgba(0, 200, 83, 0.1);
            --secondary: #00796b;
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
        
        .filter-form {
            margin-bottom: 1.5rem;
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }
        
        .filter-form select {
            padding: 0.5rem;
            border: 1px solid var(--gray);
            border-radius: 4px;
            font-size: 1rem;
            background: white;
        }
        
        .filter-form select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 5px var(--primary-light);
        }
        
        .activity-list {
            list-style: none;
            width: 100%;
        }
        
        .activity-item {
            display: flex;
            align-items: center;
            padding: 1rem;
            border-bottom: 1px solid var(--gray);
            width: 100%;
            transition: background 0.3s ease;
        }
        
        .activity-item:hover {
            background: var(--primary-light);
        }
        
        .activity-item:last-child {
            border-bottom: none;
        }
        
        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary-light);
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            flex-shrink: 0;
        }
        
        .activity-details {
            flex-grow: 1;
            min-width: 0;
        }
        
        .activity-action {
            font-weight: 600;
            color: var(--dark);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .activity-date {
            font-size: 0.85rem;
            color: #666;
        }
        
        .activity-points {
            background: var(--primary);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.85rem;
            flex-shrink: 0;
            display: flex;
            align-items: center;
        }
        
        .crown {
            margin-right: 0.5rem;
            font-size: 1.2rem;
        }
        
        .gold-crown { color: #FFD700; }
        .silver-crown { color: #C0C0C0; }
        .bronze-crown { color: #CD7F32; }
        
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
        
        .badges-container {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            width: 100%;
        }
        
        .badge {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            background: var(--gray);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
            font-size: 1.5rem;
            position: relative;
            flex-shrink: 0;
        }
        
        .badge.earned {
            background: var(--primary-light);
            border: 3px solid var(--primary);
        }
        
        .badge-tooltip {
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%);
            background: var(--dark);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-size: 0.8rem;
            white-space: nowrap;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            margin-bottom: 10px;
        }
        
        .badge:hover .badge-tooltip {
            opacity: 1;
            visibility: visible;
            margin-bottom: 15px;
        }
        
        .badge-tooltip::after {
            content: '';
            position: absolute;
            top: 100%;
            left: 50%;
            transform: translateX(-50%);
            border-width: 5px;
            border-style: solid;
            border-color: var(--dark) transparent transparent transparent;
        }
        
        .challenges-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            width: 100%;
        }
        
        .challenge-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 1rem;
            position: relative;
        }
        
        .challenge-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        }
        
        .challenge-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary-light);
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        
        .challenge-details {
            flex-grow: 1;
            min-width: 0;
        }
        
        .challenge-action {
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: var(--dark);
        }
        
        .challenge-progress {
            font-size: 0.85rem;
            color: #666;
            margin-bottom: 0.5rem;
        }

        .challenge-progress-bar {
            width: 100%;
            height: 8px;
            background: var(--gray);
            border-radius: 4px;
            overflow: hidden;
        }

        .challenge-progress-fill {
            height: 100%;
            background: var(--primary);
            border-radius: 4px;
            transition: width 0.3s ease;
        }

        .challenge-reward {
            font-size: 0.85rem;
            color: #666;
        }

        .challenge-card.completed {
            background: var(--primary-light);
            border: 2px solid var(--primary);
        }

        .challenge-completed {
            font-size: 0.85rem;
            color: var(--primary);
            font-weight: 600;
            margin-top: 0.5rem;
        }
        
        .bin-request-modal, .recycle-modal, .edit-profile-modal {
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
        
        .bin-request-modal.active, .recycle-modal.active, .edit-profile-modal.active {
            display: flex;
        }
        
        .bin-request-form, .recycle-form, .edit-profile-form {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
            animation: fadeIn 0.3s ease;
            position: relative;
            background: linear-gradient(135deg, #ffffff 0%, #f0f4f8 100%);
            border: 1px solid rgba(0, 200, 83, 0.2);
        }

        .bin-request-form h3, .recycle-form h3, .edit-profile-form h3 {
            color: var(--secondary);
            margin-bottom: 1.5rem;
            font-size: 1.8rem;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .form-group {
            margin-bottom: 1.2rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--dark);
            font-weight: 500;
        }
        
        .form-group select,
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid var(--gray);
            border-radius: 8px;
            font-size: 1rem;
            background: #ffffff;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }
        
        .form-group select:focus,
        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 10px var(--primary-light);
        }

        .form-group textarea {
            height: 100px;
            resize: vertical;
        }
        
        .submit-btn {
            background: var(--primary);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .submit-btn:hover {
            background: var(--secondary);
            transform: translateY(-2px);
        }
        
        .close-btn {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: none;
            border: none;
            font-size: 1.8rem;
            color: var(--dark);
            cursor: pointer;
            transition: color 0.3s ease;
        }
        
        .close-btn:hover {
            color: #f44336;
        }
        
        .points-display {
            margin-top: 1rem;
            color: var(--primary);
            font-weight: 600;
            text-align: center;
            font-size: 1.2rem;
        }
        
        .hidden {
            display: none;
        }
        
        /* Style du bouton Ajouter un lieu (Correspondant à l'admin) */
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
            box-shadow: 0 5px 15px rgba(0, 200, 83, 0.2);
        }
        
        /* Comportement de défilement fluide */
        html {
            scroll-behavior: smooth;
        }

        .see-location-link {
            display: inline-block;
            margin-top: 0.5rem;
            padding: 0.5rem 1rem;
            background: var(--primary-light);
            color: var(--primary);
            text-decoration: none;
            border-radius: 4px;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .see-location-link:hover {
            background: var(--primary);
            color: white;
        }

        .place-card {
            cursor: pointer;
        }

        .place-card:hover .see-location-link {
            background: var(--primary-light);
        }
        
        /* Ajustements responsives */
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
            
            .stats-cards {
                grid-template-columns: 1fr 1fr;
            }
            
            .challenges-grid {
                grid-template-columns: 1fr;
            }
            
            .filter-form {
                flex-direction: column;
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
            
            .place-image {
                height: 120px;
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
                <div class="user-avatar"><img src="uploads/<?php echo htmlspecialchars($user_profile_image); ?>" alt="Profil"></div>
                <span class="user-name"><?php echo htmlspecialchars($user_name); ?></span>
            </div>
        </div>
    </nav>

    <div class="dashboard-container">
        <aside class="sidebar">
            <ul class="sidebar-menu">
                <li><a href="#dashboard" class="active"><i class="fas fa-home"></i> Tableau de bord</a></li>
                <li><a href="#map"><i class="fas fa-map-marker-alt"></i> Carte de recyclage</a></li>
                <li><a href="#rewards"><i class="fas fa-trophy"></i> Récompenses</a></li>
                <li><a href="#bin-request"><i class="fas fa-plus"></i> Demande de poubelle</a></li>
                <li><a href="#challenges"><i class="fas fa-trophy"></i> Défis</a></li>
                <li><a href="#activity"><i class="fas fa-history"></i> Journal d'activité</a></li>
                <li><a href="#leaderboard"><i class="fas fa-crown"></i> Classement</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <section id="dashboard">
                <div class="welcome-banner animate__animated animate__fadeIn">
                    <h2>Bienvenue, <?php echo htmlspecialchars($user_name); ?> !</h2>
                    <p>Vous faites une différence ! Suivez votre impact en matière de recyclage, trouvez des poubelles à proximité et gagnez des récompenses pour vos actions écoresponsables.</p>
                </div>
                <div class="stats-cards">
                    <div class="stat-card animate__animated animate__fadeInUp">
                        <h3><i class="fas fa-coins"></i> Vos Points</h3>
                        <div class="value"><?php echo number_format($user_points); ?></div>
                    </div>
                    <div class="stat-card animate__animated animate__fadeInUp animate__delay-1s">
                        <h3><i class="fas fa-leaf"></i> Niveau</h3>
                        <div class="value"><?php echo htmlspecialchars($user_level); ?></div>
                    </div>
                    <div class="stat-card animate__animated animate__fadeInUp animate__delay-2s">
                        <h3><i class="fas fa-recycle"></i> Objets Recyclés</h3>
                        <div class="value"><?php echo number_format($items_recycled, 1); ?> kg</div>
                    </div>
                    <div class="stat-card animate__animated animate__fadeInUp animate__delay-3s">
                        <h3><i class="fas fa-weight"></i> Déchets Détournés</h3>
                        <div class="value"><?php echo number_format($items_recycled, 1); ?> kg</div>
                    </div>
                </div>
            </section>

            <section id="map">
                <div class="dashboard-card animate__animated animate__fadeIn">
                    <div class="card-header">
                        <h3>Lieux de recyclage à proximité</h3>
                        <a href="#" class="see-all" onclick="toggleSection('map', 4)">Voir tout <i class="fas fa-chevron-right"></i></a>
                    </div>
                    <form class="filter-form" id="locationFilter">
                        <div class="form-group">
                            <label for="country">Pays :</label>
                            <select id="country" name="country" onchange="updateStatesAndCities()">
                                <option value="">Tous</option>
                                <?php
                                $countries = array_keys($countries_states_cities);
                                foreach ($countries as $country) {
                                    echo "<option value='$country'>$country</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="state">Région/État :</label>
                            <select id="state" name="state" onchange="updateCities()">
                                <option value="">Tous</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="city">Ville :</label>
                            <select id="city" name="city" onchange="filterLocations()">
                                <option value="">Tous</option>
                            </select>
                        </div>
                    </form>
                    <div class="places-grid" id="placesGrid">
                        <?php foreach ($nearby_locations as $index => $location): ?>
                        <div class="place-card <?php echo $index >= 4 ? 'hidden' : ''; ?>" data-index="<?php echo $index; ?>" data-country="<?php echo htmlspecialchars($location['country']); ?>" data-state="<?php echo htmlspecialchars($location['state']); ?>" data-city="<?php echo htmlspecialchars($location['city']); ?>" onclick="showRecycleForm('<?php echo htmlspecialchars($location['name']); ?>')">
                            <img src="uploads/<?php echo htmlspecialchars($location['image']); ?>" alt="<?php echo htmlspecialchars($location['name']); ?>" class="place-image">
                            <div class="place-name"><?php echo htmlspecialchars($location['name']); ?></div>
                            <div class="place-time">Ouvert : <?php echo htmlspecialchars($location['time']); ?> - Fermé : <?php echo isset($location['closing_time']) ? htmlspecialchars($location['closing_time']) : 'N/A'; ?></div>
                            <a href="https://www.google.com/maps/search/?api=1&query=<?php echo urlencode($location['location']); ?>" target="_blank" class="see-location-link">Voir l'emplacement</a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>

            <section id="rewards">
                <div class="dashboard-card animate__animated animate__fadeIn">
                    <div class="card-header">
                        <h3>Ganger des Badges</h3>
                    </div>
                    <div class="badges-container">
                        <?php foreach ($badges as $badge): ?>
                            <div class="badge <?php echo $badge['earned'] ? 'earned' : ''; ?>">
                                <i class="<?php echo $badge['icon']; ?>"></i>
                                <span class="badge-tooltip"><?php echo htmlspecialchars($badge['name']) . ' - ' . ($badge['name'] === 'Recycleur Mensuel' ? 'Recyclez 3 mois d\'affilée' : ($badge['threshold'] . ' points')); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>

            <section id="bin-request">
                <div class="dashboard-card animate__animated animate__fadeIn">
                    <div class="card-header">
                        <h3>Demander une nouvelle poubelle de recyclage</h3>
                    </div>
                    <p>Améliorez l'accessibilité au recyclage dans votre région en demandant un nouveau point de collecte.</n> </p>
                    <button class="add-location" onclick="showBinRequestForm()"><i class="fas fa-plus"></i> Demander une nouvelle poubelle</button>
                </div>
            </section>

            <section id="challenges">
                <div class="dashboard-card animate__animated animate__fadeIn">
                    <div class="card-header">
                        <h3>Défis</h3>
                    </div>
                    <p>Relevez des défis excitants pour gagner des récompenses supplémentaires !</p>
                    <div class="challenges-grid">
                        <?php foreach ($challenges as $challenge): 
                            $percentage = $challenge['goal'] > 0 ? min(100, ($challenge['progress'] / $challenge['goal']) * 100) : 0;
                            $completed = $challenge['completed_at'] ? true : false;
                        ?>
                        <div class="challenge-card <?php echo $completed ? 'completed' : ''; ?>">
                            <div class="challenge-icon">
                                <i class="fas fa-trophy"></i>
                            </div>
                            <div class="challenge-details">
                                <div class="challenge-action"><?php echo htmlspecialchars($challenge['action']); ?></div>
                                <div class="challenge-progress"><?php echo htmlspecialchars(number_format($challenge['progress'])) . ' / ' . htmlspecialchars(number_format($challenge['goal'])); ?> pts</div>
                                <div class="challenge-progress-bar">
                                    <div class="challenge-progress-fill" style="width: <?php echo $percentage; ?>%;"></div>
                                </div>
                                <div class="challenge-reward">Récompense : <?php echo htmlspecialchars($challenge['reward']); ?> pts</div>
                                <?php if ($completed): ?>
                                    
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php if (empty($challenges)): ?>
                            <p>Aucun défi actif disponible pour le moment.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </section>
            <section id="activity">
                <div class="dashboard-card animate__animated animate__fadeIn">
                    <div class="card-header">
                        <h3>Activité récente</h3>
                        <a href="activity_log.php" class="see-all">Voir tout <i class="fas fa-chevron-right"></i></a>
                    </div>
                    <ul class="activity-list">
                        <?php foreach ($recent_activity as $activity): ?>
                        <li class="activity-item">
                            <div class="activity-icon">
                                <i class="fas fa-recycle"></i>
                            </div>
                            <div class="activity-details">
                                <div class="activity-action"><?php echo htmlspecialchars($activity['action']); ?></div>
                                <div class="activity-date"><?php echo htmlspecialchars($activity['date']); ?></div>
                            </div>
                            <div class="activity-points">+<?php echo number_format($activity['points']); ?> pts</div>
                        </li>
                        <?php endforeach; ?>
                        <?php if (empty($recent_activity)): ?>
                            <p>Aucune activité récente trouvée.</p>
                        <?php endif; ?>
                    </ul>
                </div>
            </section>

            <section id="leaderboard">
                <div class="dashboard-card animate__animated animate__fadeIn">
                    <div class="card-header">
                        <h3>Classement</h3>
                    </div>
                    <ul class="activity-list">
                        <?php
                        for ($i = 0; $i < min(5, count($leaderboard)); $i++): 
                            $leader_profile_image = $leaderboard[$i]['profile_image'] ?? 'images/person.jpg'; // Image par défaut si null
                        ?>
                        <li class="activity-item">
                            <div class="activity-icon">
                                <img src="uploads/<?php echo htmlspecialchars($leader_profile_image); ?>" alt="Profil" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                            </div>
                            <div class="activity-details">
                                <div class="activity-action">
                                    <?php if ($i === 0): ?>
                                        <i class="fas fa-crown gold-crown crown"></i>
                                    <?php elseif ($i === 1): ?>
                                        <i class="fas fa-crown silver-crown crown"></i>
                                    <?php elseif ($i === 2): ?>
                                        <i class="fas fa-crown bronze-crown crown"></i>
                                    <?php endif; ?>
                                    <?php echo htmlspecialchars($leaderboard[$i]['username']); ?> 
                                </div>
                            </div>
                            <div class="activity-points"><?php echo number_format($leaderboard[$i]['points']); ?> pts</div>
                        </li>
                        <?php endfor; ?>
                    </ul>
                </div>
            </section>
        </main>
    </div>

    <div class="user-dialog" id="userDialog">
        <div class="profile-image"><img src="uploads/<?php echo htmlspecialchars($user_profile_image); ?>" alt="Profil"></div>
        <div class="user-info">
            <h3><?php echo htmlspecialchars($user_name); ?></h3>
            <p><?php echo htmlspecialchars($user_email); ?></p>
        </div>
        <div class="dialog-buttons">
            <button onclick="alert('Changer de compte cliqué')">Changer de compte</button>
            <button onclick="alert('Supprimer le compte cliqué')" style="color: #f44336;">Supprimer le compte</button>
            <button onclick="window.location.href='index.php'">Déconnexion</button>
            <button onclick="showEditProfileForm()">Modifier le profil</button>
        </div>
    </div>

    <div class="bin-request-modal" id="binRequestModal">
        <div class="bin-request-form">
            <button class="close-btn" onclick="hideBinRequestForm()">×</button>
            <h3>Soumettre une demande de poubelle</h3>
            <form id="binRequestForm" method="post" action="">
                <input type="hidden" name="bin_request_submit" value="1">
                <div class="form-group">
                    <label for="requestCountry">Pays :</label>
                    <select id="requestCountry" name="country" required onchange="updateBinRequestStates()">
                        <option value="">Sélectionner un pays</option>
                        <?php
                        $countries = array_keys($countries_states_cities);
                        foreach ($countries as $country) {
                            echo "<option value='$country'>$country</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="requestState">Région/État :</label>
                    <select id="requestState" name="state" required onchange="updateBinRequestCities()">
                        <option value="">Sélectionner une région/état</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="requestCity">Ville :</label>
                    <select id="requestCity" name="city" required>
                        <option value="">Sélectionner une ville</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="requestLocation">Emplacement spécifique :</label>
                    <input type="text" id="requestLocation" name="location" placeholder="ex. : 123 Rue Principale" required>
                </div>
                <div class="form-group">
                    <label for="requestNotes">Notes supplémentaires :</label>
                    <textarea id="requestNotes" name="notes" placeholder="Détails supplémentaires (ex. : urgence, type de poubelle)"></textarea>
                </div>
                <button type="submit" class="submit-btn">Soumettre la demande</button>
            </form>
        </div>
    </div>

    <div class="recycle-modal" id="recycleModal">
        <div class="recycle-form">
            <button class="close-btn" onclick="hideRecycleForm()">×</button>
            <h3 id="modalLocation">Recyclage à [Emplacement]</h3>
            <form id="recycleForm" method="post" action="">
                <input type="hidden" name="recycle_submit" value="1">
                <input type="hidden" name="location_name" id="locationName">
                <div class="form-group">
                    <label for="recycleType">Type de recyclage :</label>
                    <select id="recycleType" name="recycle_type" required onchange="updatePoints()">
                        <option value="Plastique">Plastique</option>
                        <option value="Papier">Papier</option>
                        <option value="Verre">Verre</option>
                        <option value="Aluminium">Aluminium</option>
                        <option value="Carton">Carton</option>
                        <option value="Déchets électroniques">Déchets électroniques</option>
                        <option value="Textiles">Textiles</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="quantity">Quantité (kg) :</label>
                    <input type="number" id="quantity" name="quantity" min="0.1" step="0.1" value="1" required oninput="updatePoints()">
                </div>
                <div class="points-display" id="pointsDisplay">Points : 0</div>
                <button type="submit" class="submit-btn">Soumettre le recyclage</button>
            </form>
        </div>
    </div>

    <div class="edit-profile-modal" id="editProfileModal">
        <div class="edit-profile-form">
            <button class="close-btn" onclick="hideEditProfileForm()">×</button>
            <h3>Modifier les détails du profil</h3>
            <form method="post" enctype="multipart/form-data" onsubmit="submitEditProfileForm(event)">
                <input type="hidden" name="update_profile" value="1">
                <div class="form-group">
                    <label for="edit_username">Nom d'utilisateur :</label>
                    <input type="text" id="edit_username" name="username" value="<?php echo htmlspecialchars($user_name); ?>" required>
                </div>
                <div class="form-group">
                    <label for="edit_email">Email :</label>
                    <input type="email" id="edit_email" name="email" value="<?php echo htmlspecialchars($user_email); ?>" required>
                </div>
                <div class="form-group">
                    <label for="edit_password">Nouveau mot de passe (laisser vide pour conserver l'actuel) :</label>
                    <input type="password" id="edit_password" name="password">
                </div>
                <div class="form-group">
                    <label for="edit_profile_image">Image de profil :</label>
                    <input type="file" id="edit_profile_image" name="profile_image" accept="image/*">
                </div>
                <button type="submit" class="submit-btn">Mettre à jour le profil</button>
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

        function toggleSection(sectionId, initialCount) {
            const section = document.getElementById(sectionId);
            const items = section.querySelectorAll('.place-card');
            const seeAllBtn = section.querySelector('.see-all');

            items.forEach((item, index) => {
                if (index >= initialCount) {
                    item.classList.toggle('hidden');
                }
            });

            if (items[initialCount] && items[initialCount].classList.contains('hidden')) {
                seeAllBtn.textContent = 'Voir tout ';
                seeAllBtn.appendChild(document.createElement('i')).className = 'fas fa-chevron-right';
            } else {
                seeAllBtn.textContent = 'Masquer ';
                seeAllBtn.appendChild(document.createElement('i')).className = 'fas fa-chevron-up';
            }
        }

        function showRecycleForm(locationName) {
            const modal = document.getElementById('recycleModal');
            const modalLocation = document.getElementById('modalLocation');
            const locationInput = document.getElementById('locationName');
            modalLocation.textContent = `Recyclage à ${locationName}`;
            locationInput.value = locationName;
            modal.classList.add('active');
            updatePoints();
        }

        function hideRecycleForm() {
            const modal = document.getElementById('recycleModal');
            modal.classList.remove('active');
            document.getElementById('recycleForm').reset();
            document.getElementById('pointsDisplay').textContent = 'Points : 0';
        }

        function updatePoints() {
            const recycleType = document.getElementById('recycleType').value;
            const quantity = parseFloat(document.getElementById('quantity').value) || 0;
            const rate = <?php echo json_encode($recycling_rates); ?>[recycleType] || 0;
            const points = Math.round(quantity * rate);
            document.getElementById('pointsDisplay').textContent = `Points : ${points}`;
        }

        document.getElementById('recycleType').addEventListener('change', updatePoints);
        document.getElementById('quantity').addEventListener('input', updatePoints);

        function filterLocations() {
            const country = document.getElementById('country').value.toLowerCase();
            const state = document.getElementById('state').value.toLowerCase();
            const city = document.getElementById('city').value.toLowerCase();
            const placeCards = document.querySelectorAll('#placesGrid .place-card');

            placeCards.forEach(card => {
                const cardCountry = card.getAttribute('data-country').toLowerCase();
                const cardState = card.getAttribute('data-state').toLowerCase();
                const cardCity = card.getAttribute('data-city').toLowerCase();
                const matchesCountry = !country || cardCountry === country;
                const matchesState = !state || cardState === state;
                const matchesCity = !city || cardCity === city;

                if (matchesCountry && matchesState && matchesCity) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });

            const seeAllBtn = document.querySelector('#map .see-all');
            if (seeAllBtn) {
                const hiddenCards = document.querySelectorAll('#placesGrid .place-card[style="display: none;"]');
                if (hiddenCards.length === placeCards.length) {
                    seeAllBtn.textContent = 'Voir tout ';
                    seeAllBtn.appendChild(document.createElement('i')).className = 'fas fa-chevron-right';
                }
                placeCards.forEach((card, index) => {
                    if (index >= 4) card.classList.add('hidden');
                });
            }
        }

        function updateStatesAndCities() {
            const country = document.getElementById('country').value;
            const stateSelect = document.getElementById('state');
            const citySelect = document.getElementById('city');
            const statesCities = <?php echo json_encode($countries_states_cities); ?>;

            stateSelect.innerHTML = '<option value="">Tous</option>';
            citySelect.innerHTML = '<option value="">Tous</option>';

            if (country && statesCities[country]) {
                Object.keys(statesCities[country]).forEach(state => {
                    const option = document.createElement('option');
                    option.value = state;
                    option.textContent = state;
                    stateSelect.appendChild(option);
                });
            }

            updateCities();
            filterLocations();
        }

        function updateCities() {
            const country = document.getElementById('country').value;
            const state = document.getElementById('state').value;
            const citySelect = document.getElementById('city');
            const statesCities = <?php echo json_encode($countries_states_cities); ?>;

            citySelect.innerHTML = '<option value="">Tous</option>';

            if (country && state && statesCities[country] && statesCities[country][state]) {
                statesCities[country][state].forEach(city => {
                    const option = document.createElement('option');
                    option.value = city;
                    option.textContent = city;
                    citySelect.appendChild(option);
                });
            }

            filterLocations();
        }

        window.addEventListener('load', updateStatesAndCities);

        function showBinRequestForm() {
            const modal = document.getElementById('binRequestModal');
            modal.classList.add('active');
            updateBinRequestStates();
        }

        function hideBinRequestForm() {
            const modal = document.getElementById('binRequestModal');
            modal.classList.remove('active');
            document.getElementById('binRequestForm').reset();
        }

        function updateBinRequestStates() {
            const country = document.getElementById('requestCountry').value;
            const stateSelect = document.getElementById('requestState');
            const citySelect = document.getElementById('requestCity');
            const statesCities = <?php echo json_encode($countries_states_cities); ?>;

            stateSelect.innerHTML = '<option value="">Sélectionner une région/état</option>';
            citySelect.innerHTML = '<option value="">Sélectionner une ville</option>';

            if (country && statesCities[country]) {
                Object.keys(statesCities[country]).forEach(state => {
                    const option = document.createElement('option');
                    option.value = state;
                    option.textContent = state;
                    stateSelect.appendChild(option);
                });
            }
        }

        function updateBinRequestCities() {
            const country = document.getElementById('requestCountry').value;
            const state = document.getElementById('requestState').value;
            const citySelect = document.getElementById('requestCity');
            const statesCities = <?php echo json_encode($countries_states_cities); ?>;

            citySelect.innerHTML = '<option value="">Sélectionner une ville</option>';

            if (country && state && statesCities[country] && statesCities[country][state]) {
                statesCities[country][state].forEach(city => {
                    const option = document.createElement('option');
                    option.value = city;
                    option.textContent = city;
                    citySelect.appendChild(option);
                });
            }
        }

        document.getElementById('requestCountry').addEventListener('change', function() {
            updateBinRequestStates();
            updateBinRequestCities();
        });
        document.getElementById('requestState').addEventListener('change', updateBinRequestCities);

        function showEditProfileForm() {
            const modal = document.getElementById('editProfileModal');
            modal.classList.add('active');
        }

        function hideEditProfileForm() {
            const modal = document.getElementById('editProfileModal');
            modal.classList.remove('active');
            document.querySelector('#editProfileModal form').reset();
        }

    function submitEditProfileForm(event) {
        event.preventDefault();
        const form = document.querySelector('#editProfileModal form');
        const formData = new FormData(form);
        fetch('home.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                hideEditProfileForm(); // Hide the modal
                location.reload();    // Reload the page
            } else {
                alert('Failed to update profile.');
            }
        })
        .catch(error => console.error('Error:', error));
    }
</script>
</body>
</html>