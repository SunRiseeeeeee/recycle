<?php
require_once '../php_partie_admin/connection_admin.php';

$login_error = '';
$signup_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['username']) && isset($_POST['password'])) {
        if (isset($_POST['login-form'])) {
            $username = trim($_POST['username']);
            $password = trim($_POST['password']);

            if (empty($username) || empty($password)) {
                $login_error = 'Veuillez remplir tous les champs.';
            } elseif (signinAdmin($username, $password)) {
                header("Location: admin_home.php");
                exit();
            } else {
                $login_error = 'Nom d\'utilisateur ou mot de passe invalide.';
            }
        } elseif (isset($_POST['signup-form'])) {
            $username = trim($_POST['username']);
            $password = trim($_POST['password']);
            $email = trim($_POST['email']);
            $full_name = $username; // Utilisation du nom d'utilisateur comme nom complet pour simplifier ; ajuster si nécessaire

            if (empty($username) || empty($password) || empty($email)) {
                $signup_error = 'Veuillez remplir tous les champs.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $signup_error = 'Format d\'email invalide.';
            } elseif ($_FILES['profile_image']['error'] === UPLOAD_ERR_NO_FILE) {
                $signup_error = 'Veuillez télécharger une image de profil.';
            } else {
                // Vérifier si le nom d'utilisateur ou l'email existe déjà
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM admins WHERE username = :username OR email = :email");
                $stmt->execute([':username' => $username, ':email' => $email]);
                if ($stmt->fetchColumn() > 0) {
                    $signup_error = 'Nom d\'utilisateur ou email déjà existant.';
                } elseif (signupAdmin($username, $email, $password, $full_name, null)) { // Passer null s'il n'y a pas d'image
                    header("Location: admin.php"); // Rediriger vers la page de login après inscription réussie
                    exit();
                } else {
                    $signup_error = 'Inscription échouée. Veuillez réessayer.';
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
            --light: #f5f9ff;
            --gray: #e0e9f5;
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
        
        .admin-container {
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
        
        .admin-header {
            background: var(--gradient);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        
        .admin-header h2 {
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
        }
        
        .admin-header p {
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
        
        .form-group input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(0, 102, 204, 0.2);
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
            box-shadow: 0 5px 15px rgba(0, 102, 204, 0.2);
        }
        
        .error-message {
            color: #dc3545;
            font-size: 0.9rem;
            margin-top: 1rem;
            text-align: center;
            display: none;
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
            .admin-container {
                max-width: 100%;
            }
            
            .admin-header {
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
        <div class="admin-container animate__animated animate__fadeInUp">
            <div class="admin-header">
                <h2><i class="fas fa-user-shield"></i> Tableau de bord Admin</h2>
                <p>Accédez au tableau de bord d'administration de CycleBins</p>
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
                    <input type="hidden" name="login-form" value="1">
                    <div class="form-group">
                        <label for="login-username"><i class="fas fa-user"></i> Nom d'utilisateur</label>
                        <input type="text" id="login-username" name="username" placeholder="Entrez votre nom d'utilisateur" required value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="login-password"><i class="fas fa-lock"></i> Mot de passe</label>
                        <input type="password" id="login-password" name="password" placeholder="Entrez votre mot de passe" required>
                    </div>
                    
                    <div id="login-error" class="error-message" style="display: <?php echo $login_error ? 'block' : 'none'; ?>;">
                        <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($login_error); ?>
                    </div>
                    
                    <button type="submit" class="submit-btn">
                        <i class="fas fa-sign-in-alt"></i> Connexion
                    </button>
                </form>
                
                <form id="signup-form" class="form-wrapper" action="" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="signup-form" value="1">
                    <div class="form-group">
                        <label for="signup-username"><i class="fas fa-user"></i> Nom d'utilisateur</label>
                        <input type="text" id="signup-username" name="username" placeholder="Choisissez un nom d'utilisateur" required value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="signup-password"><i class="fas fa-lock"></i> Mot de passe</label>
                        <input type="password" id="signup-password" name="password" placeholder="Créez un mot de passe" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="signup-email"><i class="fas fa-envelope"></i> Email</label>
                        <input type="email" id="signup-email" name="email" placeholder="Votre adresse email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="profile_image"><i class="fas fa-image"></i> Image de profil</label>
                        <input type="file" id="profile_image" name="profile_image" accept="image/*" required>
                    </div>
                    
                    <div id="signup-error" class="error-message" style="display: <?php echo $signup_error ? 'block' : 'none'; ?>;">
                        <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($signup_error); ?>
                    </div>
                    
                    <button type="submit" class="submit-btn">
                        <i class="fas fa-user-plus"></i> Créer un compte
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="footer">
        <div>© 2023 CycleBins. Tous droits réservés.</div>
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
            document.querySelectorAll('.tab-button').forEach(btn => {
                btn.classList.remove('active');
            });
            event.currentTarget.classList.add('active');
            
            document.getElementById('login-form').classList.remove('active');
            document.getElementById('signup-form').classList.remove('active');
            document.getElementById(tab + '-form').classList.add('active');
            
            document.getElementById('login-error').style.display = 'none';
            document.getElementById('signup-error').style.display = 'none';
        }
    </script>