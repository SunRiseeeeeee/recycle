<?php
require_once '../php_partie_user/connection.php';

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tab = $_POST['tab'] ?? 'login';
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $email = $_POST['email'] ?? '';

    if ($tab === 'login') {
        if (signinUser($username, $password)) {
            header("Location: home.php");
            exit();
        } else {
            $error_message = "Nom d'utilisateur ou mot de passe invalide.";
        }
    } elseif ($tab === 'signup') {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username OR email = :email");
        $stmt->execute([':username' => $username, ':email' => $email]);
        if ($stmt->fetch()) {
            $error_message = "Nom d'utilisateur ou email déjà existant.";
        } elseif ($password !== $confirm_password) {
            $error_message = "Les mots de passe ne correspondent pas.";
        } else {
            // Gestion du téléchargement d'image
            $profile_image = null;
            if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = 'uploads/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                $file_name = uniqid() . '_' . basename($_FILES['profile_image']['name']);
                $target_file = $upload_dir . $file_name;
                $image_file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
                $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
                if (in_array($image_file_type, $allowed_types)) {
                    if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_file)) {
                        $profile_image = $file_name;
                    } else {
                        $error_message = "Erreur lors du téléchargement de l'image.";
                    }
                } else {
                    $error_message = "Seuls les fichiers JPG, JPEG, PNG et GIF sont autorisés.";
                }
            }

            if (empty($error_message)) {
                if (signupUser($username, $email, $password, $username, $profile_image)) { // Passer profile_image si défini
                    header("Location: signin.php");
                    exit();
                } else {
                    $error_message = "Inscription échouée. Veuillez réessayer.";
                    if ($profile_image && file_exists($target_file)) {
                        unlink($target_file); // Nettoyer le fichier téléchargé en cas d'échec
                    }
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion/Inscription - CycleBins</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #00c853; /* Vert vibrant */
            --primary-light: rgba(0, 200, 83, 0.1);
            --secondary: #00796b; /* Sable */
            --accent: #ffab00; /* Ambre */
            --dark: #263238; /* Bleu-gris foncé */
            --light: #f5f5f5;
            --gray: #e0e0e0;
            --gradient: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', Arial, sans-serif;
            color: var(--dark);
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
        }

        .background {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url('https://images.unsplash.com/photo-1605000797499-95a51c5269ae?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2071&q=80');
            background-size: cover;
            background-position: center;
            filter: blur(8px);
            z-index: -1;
            opacity: 0.7;
        }

        .top-bar {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            padding: 1rem 2rem;
            display: flex;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 100;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
            text-decoration: none;
        }

        .logo i {
            color: var(--primary);
        }

        .main-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 6rem 2rem 4rem;
        }
        
        .login-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 500px;
            overflow: hidden;
            border: 1px solid var(--gray);
            backdrop-filter: blur(5px);
            animation: animate__fadeInUp 0.5s;
        }
        
        .login-header {
            background: var(--gradient);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        
        .login-header h2 {
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
        }
        
        .login-header p {
            opacity: 0.9;
            font-size: 0.95rem;
        }
        
        .tab-buttons {
            display: flex;
            background: var(--gray);
        }
        
        .tab-button {
            flex: 1;
            padding: 1rem;
            border: none;
            background: transparent;
            cursor: pointer;
            font-weight: 600;
            color: var(--dark);
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        
        .tab-button.active {
            background: white;
            color: var(--primary);
            box-shadow: 0 -3px 0 var(--primary) inset;
        }
        
        .tab-button:hover {
            color: var(--primary);
        }
        
        .form-container {
            padding: 2rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--dark);
        }
        
        .form-group input,
        .form-group input[type="file"] {
            width: 100%;
            padding: 0.9rem 1.2rem;
            border: 2px solid var(--gray);
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.8);
        }
        
        .form-group input:focus,
        .form-group input[type="file"]:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(0, 200, 83, 0.2);
        }
        
        .submit-btn {
            width: 100%;
            padding: 1rem;
            background: var(--gradient);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
        }
        
        .submit-btn:hover {
            background: linear-gradient(135deg, var(--secondary) 0%, var(--primary) 100%);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 200, 83, 0.2);
        }
        
        .error-message {
            color: #dc3545;
            font-size: 0.9rem;
            margin-top: 1rem;
            text-align: center;
            display: <?php echo $error_message ? 'block' : 'none'; ?>;
        }
        
        .form-wrapper {
            display: none;
            animation: animate__fadeIn 0.5s;
        }
        
        .form-wrapper.active {
            display: block;
        }

        .footer {
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 1rem 2rem;
            text-align: center;
            font-size: 0.9rem;
            position: fixed;
            bottom: 0;
            width: 100%;
            backdrop-filter: blur(5px);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .footer a {
            color: #aaa;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .footer a:hover {
            color: white;
        }

        .social-links {
            display: flex;
            gap: 1rem;
        }

        .social-links a {
            color: white;
            font-size: 1.1rem;
        }
        
        @media (max-width: 576px) {
            .login-container {
                max-width: 100%;
            }
            
            .login-header {
                padding: 1.5rem;
            }
            
            .form-container {
                padding: 1.5rem;
            }

            .footer {
                flex-direction: column;
                gap: 0.5rem;
                padding: 0.75rem;
            }
        }
    </style>
</head>
<body>
    <div class="background"></div>

    <div class="top-bar">
        <a href="index.php" class="logo">
            <i class="fas fa-recycle"></i>
            <span>CycleBins</span>
        </a>
    </div>

    <div class="main-container">
        <div class="login-container animate__animated animate__fadeInUp">
            <div class="login-header">
                <h2><i class="fas fa-user"></i> Accès utilisateur</h2>
                <p>Connectez-vous ou inscrivez-vous pour commencer à recycler avec CycleBins</p>
            </div>
            
            <div class="tab-buttons">
                <button class="tab-button active" onclick="showTab('login')">
                    <i class="fas fa-sign-in-alt"></i> Connexion
                </button>
                <button class="tab-button" onclick="showTab('signup')">
                    <i class="fas fa-user-plus"></i> Inscription
                </button>
            </div>
            
            <div class="form-container">
                <form id="login-form" class="form-wrapper active" action="" method="post">
                    <input type="hidden" name="tab" value="login">
                    <div class="form-group">
                        <label for="login-username"><i class="fas fa-user"></i> Nom d'utilisateur</label>
                        <input type="text" id="login-username" name="username" placeholder="Entrez votre nom d'utilisateur" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="login-password"><i class="fas fa-lock"></i> Mot de passe</label>
                        <input type="password" id="login-password" name="password" placeholder="Entrez votre mot de passe" required>
                    </div>
                    
                    <div id="login-error" class="error-message">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
                    </div>
                    
                    <button type="submit" class="submit-btn">
                        <i class="fas fa-sign-in-alt"></i> Connexion
                    </button>
                </form>
                
                <form id="signup-form" class="form-wrapper" action="" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="tab" value="signup">
                    <div class="form-group">
                        <label for="signup-username"><i class="fas fa-user"></i> Nom d'utilisateur</label>
                        <input type="text" id="signup-username" name="username" placeholder="Choisissez un nom d'utilisateur" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="signup-email"><i class="fas fa-envelope"></i> Email</label>
                        <input type="email" id="signup-email" name="email" placeholder="Votre adresse email" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="signup-password"><i class="fas fa-lock"></i> Mot de passe</label>
                        <input type="password" id="signup-password" name="password" placeholder="Créez un mot de passe" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="signup-confirm-password"><i class="fas fa-lock"></i> Confirmer le mot de passe</label>
                        <input type="password" id="signup-confirm-password" name="confirm_password" placeholder="Confirmez votre mot de passe" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="profile_image"><i class="fas fa-image"></i> Image de profil</label>
                        <input type="file" id="profile_image" name="profile_image" accept="image/jpeg,image/png,image/gif">
                    </div>
                    
                    <div id="signup-error" class="error-message">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
                    </div>
                    
                    <button type="submit" class="submit-btn">
                        <i class="fas fa-user-plus"></i> S'inscrire
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="footer">
        <div>© 2025 CycleBins. Tous droits réservés.</div>
        <div class="social-links">
            <a href="#"><i class="fab fa-facebook-f"></i></a>
            <a href="#"><i class="fab fa-twitter"></i></a>
            <a href="#"><i class="fab fa-instagram"></i></a>
        </div>
        <div>
            <a href="#">Politique de confidentialité</a> | <a href="#">Conditions d'utilisation</a>
        </div>
    </div>

    <script>
        function showTab(tab) {
            document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
            document.querySelector(`.tab-button[onclick="showTab('${tab}')"]`).classList.add('active');
            
            document.getElementById('login-form').classList.remove('active');
            document.getElementById('signup-form').classList.remove('active');
            document.getElementById(`${tab}-form`).classList.add('active');
            
            document.getElementById('login-error').style.display = 'none';
            document.getElementById('signup-error').style.display = 'none';
        }
    </script>