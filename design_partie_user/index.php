<?php
session_start();
$welcome_message = "Bienvenue sur CycleBins !";

// Connexion à la base de données
try {
    $pdo = new PDO("mysql:host=localhost;dbname=cyclebins_db", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Échec de la connexion : " . $e->getMessage());
}

// Récupérer le nombre d'utilisateurs
$total_users_stmt = $pdo->prepare("SELECT COUNT(*) as total FROM users");
$total_users_stmt->execute();
$total_users = $total_users_stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Récupérer le nombre d'emplacements de recyclage
$locations_stmt = $pdo->prepare("SELECT COUNT(*) as total FROM recycling_locations");
$locations_stmt->execute();
$total_locations = $locations_stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Récupérer le total des points au lieu des kg
$total_points_stmt = $pdo->prepare("SELECT SUM(points) as total_points FROM users");
$total_points_stmt->execute();
$total_points = $total_points_stmt->fetch(PDO::FETCH_ASSOC)['total_points'] ?? 0;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CycleBins - Révolutionner le recyclage</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #00c853; /* Vert vibrant */
            --secondary: #00796b; /* Sable */
            --accent: #ffab00; /* Ambre */
            --dark: #263238; /* Bleu-gris foncé */
            --light: #f5f5f5;
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
            padding: 1.5rem 5%;
            background: var(--gradient);
            color: white;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }
        
        .navbar.scrolled {
            padding: 1rem 5%;
            background: rgba(0, 200, 83, 0.95);
            backdrop-filter: blur(10px);
        }
        
        .logo {
            font-size: 1.8rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .logo i {
            color: var(--accent);
        }
        
        .nav-buttons {
            display: flex;
            gap: 1.5rem;
        }
        
        .nav-btn {
            padding: 0.75rem 1.5rem;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 2px solid white;
            border-radius: 50px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .nav-btn:hover {
            background: white;
            color: var(--primary);
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .nav-btn i {
            font-size: 1.1rem;
        }
        
        .hero {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 0 5%;
            background: url('https://arcwoodenviro.com/wp-content/uploads/2022/04/Recycling-Graphic.png') no-repeat center center;
            background-size: cover;
            position: relative;
            margin-top: 80px;
        }
        
        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
        }
        
        .hero-content {
            position: relative;
            z-index: 1;
            color: white;
            max-width: 800px;
        }
        
        .hero h1 {
            font-size: 3.5rem;
            margin-bottom: 1.5rem;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }
        
        .hero p {
            font-size: 1.2rem;
            margin-bottom: 2.5rem;
            line-height: 1.6;
        }
        
        .cta-button {
            padding: 1rem 2.5rem;
            background: var(--accent);
            color: var(--dark);
            border: none;
            border-radius: 50px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            box-shadow: 0 4px 15px rgba(255, 171, 0, 0.4);
        }
        
        .cta-button:hover {
            background: white;
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(255, 171, 0, 0.6);
        }
        
        .features {
            padding: 6rem 5%;
            background: white;
        }
        
        .section-title {
            text-align: center;
            margin-bottom: 4rem;
            font-size: 2.5rem;
            color: var(--secondary);
            position: relative;
        }
        
        .section-title::after {
            content: '';
            position: absolute;
            bottom: -15px;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 4px;
            background: var(--accent);
            border-radius: 2px;
        }
        
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2.5rem;
        }
        
        .feature-card {
            background: white;
            border-radius: 15px;
            padding: 2.5rem 2rem;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 35px rgba(0, 200, 83, 0.1);
        }
        
        .feature-icon {
            font-size: 3rem;
            color: var(--primary);
            margin-bottom: 1.5rem;
        }
        
        .feature-card h3 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            color: var(--secondary);
        }
        
        .feature-card p {
            color: #666;
            line-height: 1.6;
        }
        
        .stats {
            padding: 5rem 5%;
            background: var(--gradient);
            color: white;
            text-align: center;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
            margin-top: 3rem;
        }
        
        .stat-item {
            padding: 2rem;
        }
        
        .stat-number {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            font-size: 1.1rem;
            opacity: 0.9;
        }
        
        .footer {
            background: var(--dark);
            color: white;
            padding: 4rem 5% 2rem;
            text-align: center;
        }
        
        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
            text-align: left;
        }
        
        .footer-column h3 {
            font-size: 1.3rem;
            margin-bottom: 1.5rem;
            color: var(--accent);
        }
        
        .footer-column ul {
            list-style: none;
        }
        
        .footer-column ul li {
            margin-bottom: 0.75rem;
        }
        
        .footer-column ul li a {
            color: #ccc;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        
        .footer-column ul li a:hover {
            color: white;
        }
        
        .social-links {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
        }
        
        .social-links a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            color: white;
            transition: all 0.3s ease;
        }
        
        .social-links a:hover {
            background: var(--accent);
            transform: translateY(-3px);
        }
        
        .copyright {
            padding-top: 2rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            font-size: 0.9rem;
            color: #aaa;
        }
        
        /* Animations */
        @keyframes float {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-15px);
            }
        }
        
        .floating {
            animation: float 4s ease-in-out infinite;
        }
        
        .delay-1 {
            animation-delay: 0.2s;
        }
        
        .delay-2 {
            animation-delay: 0.4s;
        }
        
        .delay-3 {
            animation-delay: 0.6s;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .hero h1 {
                font-size: 2.5rem;
            }
            
            .navbar {
                padding: 1rem 5%;
                flex-direction: column;
                gap: 1rem;
            }
            
            .nav-buttons {
                width: 100%;
                justify-content: center;
            }
            
            .hero {
                margin-top: 120px;
            }
        }
    </style>
</head>
<body>
    <div class="navbar" id="navbar">
        <div class="logo">
            <i class="fas fa-recycle"></i>
            <span>CycleBins</span>
        </div>
        <div class="nav-buttons">
            <button class="nav-btn" onclick="window.location.href='signin.php'">
                <i class="fas fa-sign-in-alt"></i> Connexion
            </button>
            <button class="nav-btn" onclick="checkAdminPassword()">
                <i class="fas fa-user-shield"></i> Administrateurs
            </button>
        </div>
    </div>

    <section class="hero">
        <div class="hero-content animate__animated animate__fadeInUp">
            <h1><?php echo $welcome_message; ?></h1>
            <p>Rejoignez CycleBins pour révolutionner le recyclage ! Notre plateforme innovante vous aide à gérer vos déchets efficacement, à trouver facilement des points de collecte et à gagner des récompenses excitantes pour contribuer à une planète plus verte.</p>
            <button class="cta-button animate__animated animate__pulse animate__infinite" onclick="window.location.href='signin.php'">
                Commencer
                <i class="fas fa-arrow-right"></i>
            </button>
        </div>
    </section>

    <section class="features">
        <h2 class="section-title animate__animated animate__fadeIn">Pourquoi choisir CycleBins ?</h2>
        <div class="features-grid">
            <div class="feature-card floating">
                <div class="feature-icon">
                    <i class="fas fa-map-marked-alt"></i>
                </div>
                <h3>Localisateur intelligent</h3>
                <p>Trouvez des poubelles et centres de recyclage près de chez vous avec notre système de cartographie intelligent qui se met à jour en temps réel.</p>
            </div>
            <div class="feature-card floating delay-1">
                <div class="feature-icon">
                    <i class="fas fa-coins"></i>
                </div>
                <h3>Programme de récompenses</h3>
                <p>Gagnez des points pour chaque action de recyclage et échangez-les contre des réductions, des produits ou des dons à des causes écologiques.</p>
            </div>
            <div class="feature-card floating delay-2">
                <div class="feature-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <h3>Suivi de l'impact</h3>
                <p>Surveillez votre contribution environnementale avec des analyses détaillées et des rapports personnalisés.</p>
            </div>
        </div>
    </section>

    <section class="stats">
        <h2 class="section-title animate__animated animate__fadeIn">Notre impact</h2>
        <div class="stats-grid">
            <div class="stat-item animate__animated animate__fadeInUp">
                <div class="stat-number"><?php echo $total_users; ?></div>
                <div class="stat-label">Utilisateurs actifs</div>
            </div>
            <div class="stat-item animate__animated animate__fadeInUp animate__delay-1s">
                <div class="stat-number"><?php echo $total_locations; ?></div>
                <div class="stat-label">Lieux de recyclage</div>
            </div>
            <div class="stat-item animate__animated animate__fadeInUp animate__delay-2s">
                <div class="stat-number"><?php echo number_format($total_points); ?></div>
                <div class="stat-label">Points cumulés</div>
            </div>
        </div>
    </section>

    <footer class="footer">
        <div class="footer-content">
            <div class="footer-column">
                <h3>À propos de CycleBins</h3>
                <p>Nous avons pour mission de rendre le recyclage simple et gratifiant pour tous, en créant une planète plus propre une poubelle à la fois.</p>
                <div class="social-links">
                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-linkedin-in"></i></a>
                </div>
            </div>
            <div class="footer-column">
                <h3>Liens rapides</h3>
                <ul>
                    <li><a href="#">Accueil</a></li>
                    <li><a href="#">Fonctionnalités</a></li>
                    <li><a href="#">Comment ça marche</a></li>
                    <li><a href="#">Témoignages</a></li>
                    <li><a href="#" onclick="checkAdminPassword()">Administrateurs</a></li>
                </ul>
            </div>
            <div class="footer-column">
                <h3>Ressources</h3>
                <ul>
                    <li><a href="#">Guide du recyclage</a></li>
                    <li><a href="#">Conseils de durabilité</a></li>
                    <li><a href="#">Forum communautaire</a></li>
                    <li><a href="#">FAQ</a></li>
                </ul>
            </div>
            <div class="footer-column">
                <h3>Contactez-nous</h3>
                <ul>
                    <li><i class="fas fa-envelope"></i> cyclebins@info.com</li>
                    <li><i class="fas fa-phone"></i> +1 (555) 123-4567</li>
                    <li><i class="fas fa-map-marker-alt"></i> 123 Rue Verte, Ville Éco</li>
                </ul>
            </div>
        </div>
        <div class="copyright">
            © 2025 CycleBins. Tous droits réservés. | <a href="#">Politique de confidentialité</a> | <a href="#">Conditions d'utilisation</a>
        </div>
    </footer>

    <script>
        // Effet de défilement de la barre de navigation
        window.addEventListener('scroll', function() {
            const navbar = document.getElementById('navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });
        
        // Animer les éléments lorsqu'ils apparaissent dans le champ de vision
        const animateOnScroll = function() {
            const elements = document.querySelectorAll('.feature-card, .stat-item, .section-title');
            
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

        // Vérification du mot de passe pour les administrateurs
        function checkAdminPassword() {
            let password = prompt("Veuillez entrer le mot de passe administrateur :");
            if (password === "1234") {
                window.location.href = "../design_partie_admin/admin.php";
            } else {
                alert("Mot de passe incorrect. Accès refusé.");
            }
        }
    </script>