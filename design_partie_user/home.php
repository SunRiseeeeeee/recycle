<?php
session_start();
// Placeholder user data - replace with database queries
$user_name = "Alex Green";
$user_email = "alex.green@example.com"; // Added email for dialog
$user_points = 1250;
$user_level = "Recycling Hero";
$recent_activity = [
    ["action" => "Recycled Plastic", "points" => 50, "date" => "2025-06-15"],
    ["action" => "Recycled Paper", "points" => 30, "date" => "2025-06-14"],
    ["action" => "Recycled Glass", "points" => 40, "date" => "2025-06-12"],
    ["action" => "Recycled Aluminum", "points" => 45, "date" => "2025-06-10"],
    ["action" => "Recycled Cardboard", "points" => 35, "date" => "2025-06-09"],
    ["action" => "Recycled E-Waste", "points" => 60, "date" => "2025-06-08"],
    ["action" => "Recycled Textiles", "points" => 25, "date" => "2025-06-07"]
];
// Enhanced nearby locations with North African countries, states, and cities
$nearby_locations = [
    ["name" => "Casablanca Recycling Center", "time" => "8 AM - 8 PM", "location" => "1.2 km away, 123 Blvd", "image" => "bin1.jpg", "country" => "Morocco", "state" => "Casablanca Settat", "city" => "Casablanca"],
    ["name" => "Algiers Green Depot", "time" => "9 AM - 6 PM", "location" => "2.5 km away, 456 Rue", "image" => "bin2.jpg", "country" => "Algeria", "state" => "Algiers", "city" => "Algiers"],
    ["name" => "Tunis Eco Park", "time" => "7 AM - 7 PM", "location" => "3.0 km away, 789 Ave", "image" => "bin3.jpg", "country" => "Tunisia", "state" => "Tunis", "city" => "Tunis"],
    ["name" => "Tripoli Reuse Center", "time" => "10 AM - 5 PM", "location" => "4.2 km away, 101 St", "image" => "bin4.jpg", "country" => "Libya", "state" => "Tripoli", "city" => "Tripoli"],
    ["name" => "Cairo Recycling Hub", "time" => "8 AM - 7 PM", "location" => "5.0 km away, 202 Rd", "image" => "bin5.jpg", "country" => "Egypt", "state" => "Cairo", "city" => "Cairo"],
    ["name" => "Khartoum Green Point", "time" => "9 AM - 6 PM", "location" => "3.8 km away, 303 St", "image" => "bin6.jpg", "country" => "Sudan", "state" => "Khartoum", "city" => "Khartoum"],
    ["name" => "Laayoune Recycle Depot", "time" => "8 AM - 8 PM", "location" => "1.5 km away, 404 Blvd", "image" => "bin7.jpg", "country" => "Western Sahara", "state" => "Laâyoune Sakia El Hamra", "city" => "Laayoune"],
    ["name" => "Nouakchott Eco Center", "time" => "9 AM - 6 PM", "location" => "2.0 km away, 505 Ave", "image" => "bin8.jpg", "country" => "Mauritania", "state" => "Nouakchott Nord", "city" => "Nouakchott"],
    ["name" => "Juba Sustainability Hub", "time" => "7 AM - 7 PM", "location" => "3.2 km away, 606 Rd", "image" => "bin9.jpg", "country" => "South Sudan", "state" => "Central Equatoria", "city" => "Juba"],
    ["name" => "N'Djamena Recycle Point", "time" => "10 AM - 5 PM", "location" => "4.0 km away, 707 St", "image" => "bin10.jpg", "country" => "Chad", "state" => "N'Djamena", "city" => "N'Djamena"]
];
// Define states and cities for all 10 countries
$countries_states_cities = [
    "Morocco" => [
        "Casablanca Settat" => ["Casablanca", "Mohammedia", "Settat", "Berrechid", "El Jadida"],
        "Marrakesh Safi" => ["Marrakesh", "Safi", "Essaouira", "Youssoufia", "Kelaa des Sraghna"],
        "Fès Meknès" => ["Fès", "Meknès", "Taza", "Sefrou", "Ifrane"],
        "Tangier Tetouan Al Hoceima" => ["Tangier", "Tetouan", "Al Hoceima", "Larache", "Chefchaouen"],
        "Rabat Salé Kénitra" => ["Rabat", "Salé", "Kénitra", "Skhirat-Témara", "Sidi Kacem"],
        "Béni Mellal Khénifra" => ["Béni Mellal", "Khénifra", "Azilal"],
        "Drâa Tafilalet" => ["Errachidia", "Ouarzazate", "Zagora"],
        "Souss Massa" => ["Agadir", "Inezgane", "Tiznit"],
        "Guelmim Oued Noun" => ["Guelmim", "Tan Tan", "Sidi Ifni"],
        "Laâyoune Sakia El Hamra" => ["Laayoune", "Boujdour", "Tarfaya"],
        "Dakhla Oued Ed Dahab" => ["Dakhla", "Oued Ed Dahab", "Bir Gandouz"]
    ],
    "Algeria" => [
        "Algiers" => ["Algiers", "Birkhadem", "Bab El Oued", "Hussein Dey", "Kouba"],
        "Oran" => ["Oran", "Arzew", "Bir El Djir", "Aïn El Turk", "Es Senia"],
        "Constantine" => ["Constantine", "Hamma Bouziane", "El Khroub", "Zighoud Youcef", "Aïn Smara"],
        "Annaba" => ["Annaba", "El Bouni", "Seraidi", "El Hadjar", "Berrahal"],
        "Tizi Ouzou" => ["Tizi Ouzou", "Azazga", "Boghni", "Draâ Ben Khedda", "Mekla"],
        "Batna" => ["Batna", "Merouana", "Timgad", "Aïn Touta"],
        "Béjaïa" => ["Béjaïa", "Amizour", "Kherrata", "Sidi Aïch"],
        "Biskra" => ["Biskra", "Sidi Okba", "El Kantara"],
        "Blida" => ["Blida", "Boufarik", "Bouïnane", "Oued El Alleug"],
        "Oran-extended" => ["Saïda", "Mascara", "Tlemcen", "Sidi Bel Abbès", "Mostaganem"],
        "Setif" => ["Setif", "El Eulma", "Aïn Oulmene"],
        "Djelfa" => ["Djelfa", "Aïn Oussera", "Messâad"],
        "Ghardaïa" => ["Ghardaïa", "Metlili", "El Menea"],
        "Tamanrasset" => ["Tamanrasset", "In Salah", "In Guezzam"]
    ],
    "Tunisia" => [
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
        "Medenine" => ["Medenine", "Ben Gardane", "Zarzis", "Houmt Souk"],
        "Tozeur" => ["Tozeur", "Nefta", "Degache"],
        "Kébili" => ["Kébili", "Douz", "Souk Lahad"],
        "Tataouine" => ["Tataouine", "Remada", "Ghomrassen", "Bir Lahmar"]
    ],
    "Libya" => [
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
    "Mauritania" => [
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
// Define challenges with progress
$challenges = [
    ["action" => "Recycle 100 Items", "reward" => "200 pts", "goal" => 100, "progress" => 65],
    ["action" => "Go Plastic-Free Week", "reward" => "150 pts", "goal" => 7, "progress" => 4],
    ["action" => "Recycle 50 Aluminum Cans", "reward" => "100 pts", "goal" => 50, "progress" => 30],
    ["action" => "Reduce Waste by 5kg", "reward" => "250 pts", "goal" => 5, "progress" => 3],
    ["action" => "Recycle 20 Glass Bottles", "reward" => "80 pts", "goal" => 20, "progress" => 12],
    ["action" => "Complete Monthly Eco Goal", "reward" => "300 pts", "goal" => 1, "progress" => 0]
];
// Define recycling points (points per kg or unit)
$recycling_rates = [
    "Plastic" => 10,
    "Paper" => 5,
    "Glass" => 8,
    "Aluminum" => 12,
    "Cardboard" => 6,
    "E-Waste" => 15,
    "Textiles" => 4
];
// Placeholder leaderboard data
$leaderboard = [
    ["name" => "Sara Brown", "level" => "Eco Champion", "points" => 3200, "activities" => 85],
    ["name" => "Mohammed Ali", "level" => "Green Warrior", "points" => 2450, "activities" => 65],
    ["name" => "Fatima Zahra", "level" => "Recycling Hero", "points" => 1250, "activities" => 42],
    ["name" => "John Doe", "level" => "Eco Starter", "points" => 950, "activities" => 30],
    ["name" => "Amina Khalil", "level" => "Green Scout", "points" => 780, "activities" => 25]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CycleBins Dashboard</title>
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
        
        .recycle-modal {
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
        
        .recycle-modal.active {
            display: flex;
        }
        
        .recycle-form {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            width: 90%;
            max-width: 400px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            animation: fadeIn 0.3s ease;
        }
        
        .recycle-form h3 {
            color: var(--secondary);
            margin-bottom: 1rem;
            font-size: 1.5rem;
        }
        
        .form-group {
            margin-bottom: 1rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--dark);
        }
        
        .form-group select,
        .form-group input {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid var(--gray);
            border-radius: 4px;
            font-size: 1rem;
        }
        
        .form-group select:focus,
        .form-group input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 5px var(--primary-light);
        }
        
        .points-display {
            margin-top: 1rem;
            color: var(--primary);
            font-weight: 600;
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
        }
        
        .submit-btn:hover {
            background: var(--secondary);
        }
        
        .close-btn {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: none;
            border: none;
            font-size: 1.5rem;
            color: var(--dark);
            cursor: pointer;
        }
        
        .hidden {
            display: none;
        }
        
        /* Smooth scrolling behavior */
        html {
            scroll-behavior: smooth;
        }
        
        /* Responsive adjustments */
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
                <div class="user-avatar"><img src="images/person.jpg" alt="Profile"></div>
                <span class="user-name"><?php echo $user_name; ?></span>
            </div>
        </div>
    </nav>

    <div class="dashboard-container">
        <aside class="sidebar">
            <ul class="sidebar-menu">
                <li><a href="#dashboard" class="active"><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="#map"><i class="fas fa-map-marker-alt"></i> Recycling Map</a></li>
                <li><a href="#rewards"><i class="fas fa-trophy"></i> Rewards</a></li>
                <li><a href="#statistics"><i class="fas fa-chart-line"></i> Statistics</a></li>
                <li><a href="#recycling-log"><i class="fas fa-recycle"></i> Recycling Log</a></li>
                <li><a href="#challenges"><i class="fas fa-trophy"></i> Challenges</a></li>
                <li><a href="#leaderboard"><i class="fas fa-crown"></i> Leaderboard</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <!-- Dashboard Section -->
            <section id="dashboard">
                <div class="welcome-banner animate__animated animate__fadeIn">
                    <h2>Welcome back, <?php echo $user_name; ?>!</h2>
                    <p>You're making a difference! Track your recycling impact, find nearby bins, and earn rewards for your eco-friendly actions.</p>
                </div>
                <div class="stats-cards">
                    <div class="stat-card animate__animated animate__fadeInUp">
                        <h3><i class="fas fa-coins"></i> Your Points</h3>
                        <div class="value"><?php echo number_format($user_points); ?></div>
                    </div>
                    <div class="stat-card animate__animated animate__fadeInUp animate__delay-1s">
                        <h3><i class="fas fa-leaf"></i> Level</h3>
                        <div class="value"><?php echo $user_level; ?></div>
                    </div>
                    <div class="stat-card animate__animated animate__fadeInUp animate__delay-2s">
                        <h3><i class="fas fa-recycle"></i> Items Recycled</h3>
                        <div class="value">42</div>
                    </div>
                    <div class="stat-card animate__animated animate__fadeInUp animate__delay-3s">
                        <h3><i class="fas fa-weight"></i> Waste Diverted</h3>
                        <div class="value">18.5 kg</div>
                    </div>
                </div>
            </section>

            <!-- Recycling Map Section (Now Places List) -->
            <section id="map">
                <div class="dashboard-card animate__animated animate__fadeIn">
                    <div class="card-header">
                        <h3>Nearby Recycling Locations</h3>
                        <a href="#" class="see-all" onclick="toggleSection('map', 4)">View All <i class="fas fa-chevron-right"></i></a>
                    </div>
                    <form class="filter-form" id="locationFilter">
                        <div class="form-group">
                            <label for="country">Country:</label>
                            <select id="country" name="country" onchange="updateStatesAndCities()">
                                <option value="">All</option>
                                <?php
                                $countries = array_keys($countries_states_cities);
                                foreach ($countries as $country) {
                                    echo "<option value='$country'>$country</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="state">State/Region:</label>
                            <select id="state" name="state" onchange="updateCities()">
                                <option value="">All</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="city">City:</label>
                            <select id="city" name="city" onchange="filterLocations()">
                                <option value="">All</option>
                            </select>
                        </div>
                    </form>
                    <div class="places-grid" id="placesGrid">
                        <?php foreach ($nearby_locations as $index => $location): ?>
                        <div class="place-card <?php echo $index >= 4 ? 'hidden' : ''; ?>" data-index="<?php echo $index; ?>" data-country="<?php echo $location['country']; ?>" data-state="<?php echo $location['state']; ?>" data-city="<?php echo $location['city']; ?>" onclick="showRecycleForm('<?php echo $location['name']; ?>')">
                            <img src="images/<?php echo $location['image']; ?>" alt="<?php echo $location['name']; ?>" class="place-image">
                            <div class="place-name"><?php echo $location['name']; ?></div>
                            <div class="place-time">Open: <?php echo $location['time']; ?></div>
                            <div class="place-location"><?php echo $location['location']; ?></div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>

            <!-- Rewards Section -->
            <section id="rewards">
                <div class="dashboard-card animate__animated animate__fadeIn">
                    <div class="card-header">
                        <h3>Your Badges</h3>
                        <a href="#" class="see-all">View All <i class="fas fa-chevron-right"></i></a>
                    </div>
                    <div class="badges-container">
                        <div class="badge earned">
                            <i class="fas fa-seedling"></i>
                            <span class="badge-tooltip">Green Starter - Recycled 10 items</span>
                        </div>
                        <div class="badge earned">
                            <i class="fas fa-medal"></i>
                            <span class="badge-tooltip">Plastic Hero - Recycled 50 plastic items</span>
                        </div>
                        <div class="badge">
                            <i class="fas fa-award"></i>
                            <span class="badge-tooltip">Eco Champion - Reach 1000 points</span>
                        </div>
                        <div class="badge">
                            <i class="fas fa-star"></i>
                            <span class="badge-tooltip">Monthly Recycler - Recycle 3 months in a row</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Statistics Section -->
            <section id="statistics">
                <div class="dashboard-card animate__animated animate__fadeIn">
                    <div class="card-header">
                        <h3>Quick Actions</h3>
                    </div>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem;">
                        <button style="padding: 1rem; background: var(--primary-light); border: none; border-radius: 8px; color: var(--primary); cursor: pointer; transition: all 0.3s ease; display: flex; flex-direction: column; align-items: center; gap: 0.5rem;">
                            <i class="fas fa-plus-circle" style="font-size: 1.5rem;"></i>
                            <span>Log Recycling</span>
                        </button>
                        <button style="padding: 1rem; background: var(--primary-light); border: none; border-radius: 8px; color: var(--primary); cursor: pointer; transition: all 0.3s ease; display: flex; flex-direction: column; align-items: center; gap: 0.5rem;">
                            <i class="fas fa-gift" style="font-size: 1.5rem;"></i>
                            <span>Redeem Rewards</span>
                        </button>
                        <button style="padding: 1rem; background: var(--primary-light); border: none; border-radius: 8px; color: var(--primary); cursor: pointer; transition: all 0.3s ease; display: flex; flex-direction: column; align-items: center; gap: 0.5rem;">
                            <i class="fas fa-map-marked-alt" style="font-size: 1.5rem;"></i>
                            <span>Find Bins</span>
                        </button>
                        <button style="padding: 1rem; background: var(--primary-light); border: none; border-radius: 8px; color: var(--primary); cursor: pointer; transition: all 0.3s ease; display: flex; flex-direction: column; align-items: center; gap: 0.5rem;">
                            <i class="fas fa-chart-pie" style="font-size: 1.5rem;"></i>
                            <span>View Stats</span>
                        </button>
                    </div>
                </div>
            </section>

            <!-- Recycling Log Section -->
            <section id="recycling-log">
                <div class="dashboard-card animate__animated animate__fadeIn">
                    <div class="card-header">
                        <h3>Recent Activity</h3>
                        <a href="activity_log.php" class="see-all">See All <i class="fas fa-chevron-right"></i></a>
                    </div>
                    <ul class="activity-list">
                        <?php for ($i = 0; $i < min(4, count($recent_activity)); $i++): ?>
                        <li class="activity-item">
                            <div class="activity-icon">
                                <i class="fas fa-recycle"></i>
                            </div>
                            <div class="activity-details">
                                <div class="activity-action"><?php echo $recent_activity[$i]['action']; ?></div>
                                <div class="activity-date"><?php echo $recent_activity[$i]['date']; ?></div>
                            </div>
                            <div class="activity-points">+<?php echo $recent_activity[$i]['points']; ?> pts</div>
                        </li>
                        <?php endfor; ?>
                    </ul>
                </div>
            </section>

            <!-- Challenges Section -->
            <section id="challenges">
                <div class="dashboard-card animate__animated animate__fadeIn">
                    <div class="card-header">
                        <h3>Challenges</h3>
                    </div>
                    <p>Take on exciting challenges to earn extra rewards!</p>
                    <div class="challenges-grid">
                        <?php foreach ($challenges as $challenge): 
                            $percentage = min(100, ($challenge['progress'] / $challenge['goal']) * 100);
                        ?>
                        <div class="challenge-card">
                            <div class="challenge-icon">
                                <i class="fas fa-trophy"></i>
                            </div>
                            <div class="challenge-details">
                                <div class="challenge-action"><?php echo $challenge['action']; ?></div>
                                <div class="challenge-progress"><?php echo $challenge['progress'] . '/' . $challenge['goal']; ?></div>
                                <div class="challenge-progress-bar">
                                    <div class="challenge-progress-fill" style="width: <?php echo $percentage; ?>%;"></div>
                                </div>
                                <div class="challenge-reward">Reward: <?php echo $challenge['reward']; ?></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>

            <!-- Leaderboard Section -->
            <section id="leaderboard">
                <div class="dashboard-card animate__animated animate__fadeIn">
                    <div class="card-header">
                        <h3>Leaderboard</h3>
                        <a href="#" class="see-all">View All <i class="fas fa-chevron-right"></i></a>
                    </div>
                    <ul class="activity-list">
                        <?php
                        // Sort leaderboard by activities count in descending order
                        usort($leaderboard, function($a, $b) {
                            return $b['activities'] - $a['activities'];
                        });
                        for ($i = 0; $i < min(5, count($leaderboard)); $i++): ?>
                        <li class="activity-item">
                            <div class="activity-icon">
                                <i class="fas fa-user"></i>
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
                                    <?php echo $leaderboard[$i]['name']; ?> (Level: <?php echo $leaderboard[$i]['level']; ?>)
                                </div>
                                <div class="activity-date">Activities: <?php echo $leaderboard[$i]['activities']; ?></div>
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
        <div class="profile-image"><img src="images/person.jpg" alt="Profile"></div>
        <div class="user-info">
            <h3><?php echo $user_name; ?></h3>
            <p><?php echo $user_email; ?></p>
        </div>
        <div class="dialog-buttons">
            <button onclick="alert('Switch Account clicked')">Switch Account</button>
            <button onclick="alert('Delete Account clicked')" style="color: #f44336;">Delete Account</button>
            <button onclick="window.location.href='index.php'">Logout</button>
        </div>
    </div>

    <div class="recycle-modal" id="recycleModal">
        <div class="recycle-form">
            <button class="close-btn" onclick="hideRecycleForm()">×</button>
            <h3 id="modalLocation">Recycling at [Location]</h3>
            <form id="recycleForm" onsubmit="submitRecycleForm(event)">
                <div class="form-group">
                    <label for="recycleType">Recycle Type:</label>
                    <select id="recycleType" name="recycleType" required>
                        <option value="Plastic">Plastic</option>
                        <option value="Paper">Paper</option>
                        <option value="Glass">Glass</option>
                        <option value="Aluminum">Aluminum</option>
                        <option value="Cardboard">Cardboard</option>
                        <option value="E-Waste">E-Waste</option>
                        <option value="Textiles">Textiles</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="quantity">Quantity (kg):</label>
                    <input type="number" id="quantity" name="quantity" min="0.1" step="0.1" value="1" required>
                </div>
                <div class="points-display" id="pointsDisplay">Points: 0</div>
                <button type="submit" class="submit-btn">Submit Recycling</button>
            </form>
        </div>
    </div>

    <script>
        // Toggle user dialog
        function toggleDialog() {
            const dialog = document.getElementById('userDialog');
            dialog.classList.toggle('active');
        }

        // Close dialog when clicking outside
        document.addEventListener('click', function(event) {
            const dialog = document.getElementById('userDialog');
            const userMenu = document.querySelector('.user-menu');
            if (!userMenu.contains(event.target) && !dialog.contains(event.target)) {
                dialog.classList.remove('active');
            }
        });

        // Sidebar active state on click
        document.querySelectorAll('.sidebar-menu a').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                document.querySelectorAll('.sidebar-menu a').forEach(a => a.classList.remove('active'));
                this.classList.add('active');
                const targetId = this.getAttribute('href').substring(1);
                document.getElementById(targetId).scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
        });

        // Update active state based on scroll position
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

        // Initial animation trigger
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

        // Function to toggle section visibility
        function toggleSection(sectionId, initialCount) {
            const section = document.getElementById(sectionId);
            const items = section.querySelectorAll('.place-card');
            const seeAllBtn = section.querySelector('.see-all');

            items.forEach((item, index) => {
                if (index >= initialCount) {
                    item.classList.toggle('hidden');
                }
            });

            // Update button text based on visibility
            if (items[initialCount] && items[initialCount].classList.contains('hidden')) {
                seeAllBtn.textContent = 'View All ';
                seeAllBtn.appendChild(document.createElement('i')).className = 'fas fa-chevron-right';
            } else {
                seeAllBtn.textContent = 'Hide ';
                seeAllBtn.appendChild(document.createElement('i')).className = 'fas fa-chevron-up';
            }
        }

        // Show recycle form
        function showRecycleForm(locationName) {
            const modal = document.getElementById('recycleModal');
            const modalLocation = document.getElementById('modalLocation');
            modalLocation.textContent = `Recycling at ${locationName}`;
            modal.classList.add('active');
            updatePoints();
        }

        // Hide recycle form
        function hideRecycleForm() {
            const modal = document.getElementById('recycleModal');
            modal.classList.remove('active');
            document.getElementById('recycleForm').reset();
            document.getElementById('pointsDisplay').textContent = 'Points: 0';
        }

        // Update points display
        function updatePoints() {
            const recycleType = document.getElementById('recycleType').value;
            const quantity = parseFloat(document.getElementById('quantity').value) || 0;
            const rate = <?php echo json_encode($recycling_rates); ?>[recycleType] || 0;
            const points = Math.round(quantity * rate);
            document.getElementById('pointsDisplay').textContent = `Points: ${points}`;
        }

        // Submit recycle form
        function submitRecycleForm(event) {
            event.preventDefault();
            const recycleType = document.getElementById('recycleType').value;
            const quantity = parseFloat(document.getElementById('quantity').value);
            const points = Math.round(quantity * <?php echo json_encode($recycling_rates); ?>[recycleType]);
            const locationName = document.getElementById('modalLocation').textContent.replace('Recycling at ', '');
            const date = new Date().toISOString().split('T')[0];

            // Simulate adding to recent activity (for demo purposes)
            const activity = {
                action: `Recycled ${recycleType} (${quantity} kg) at ${locationName}`,
                points: points,
                date: date
            };
            console.log('Recycled:', activity);

            // Update user points (simulated)
            let currentPoints = parseInt('<?php echo $user_points; ?>') || 0;
            currentPoints += points;
            document.querySelector('.value').textContent = currentPoints.toLocaleString();

            // Hide modal and reset
            hideRecycleForm();
            alert(`Successfully recycled ${quantity} kg of ${recycleType}! You earned ${points} points.`);
        }

        // Update points on input change
        document.getElementById('recycleType').addEventListener('change', updatePoints);
        document.getElementById('quantity').addEventListener('input', updatePoints);

        // Filter locations
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

            // Reset view all toggle if filtered
            const seeAllBtn = document.querySelector('#map .see-all');
            if (seeAllBtn) {
                const hiddenCards = document.querySelectorAll('#placesGrid .place-card[style="display: none;"]');
                if (hiddenCards.length === placeCards.length) {
                    seeAllBtn.textContent = 'View All ';
                    seeAllBtn.appendChild(document.createElement('i')).className = 'fas fa-chevron-right';
                }
                placeCards.forEach((card, index) => {
                    if (index >= 4) card.classList.add('hidden');
                });
            }
        }

        // Update states based on country selection
        function updateStatesAndCities() {
            const country = document.getElementById('country').value;
            const stateSelect = document.getElementById('state');
            const citySelect = document.getElementById('city');
            const statesCities = <?php echo json_encode($countries_states_cities); ?>;

            // Clear current options
            stateSelect.innerHTML = '<option value="">All</option>';
            citySelect.innerHTML = '<option value="">All</option>';

            if (country && statesCities[country]) {
                Object.keys(statesCities[country]).forEach(state => {
                    const option = document.createElement('option');
                    option.value = state;
                    option.textContent = state;
                    stateSelect.appendChild(option);
                });
            }

            // Trigger city update if state is selected
            updateCities();
            filterLocations();
        }

        // Update cities based on state selection
        function updateCities() {
            const country = document.getElementById('country').value;
            const state = document.getElementById('state').value;
            const citySelect = document.getElementById('city');
            const statesCities = <?php echo json_encode($countries_states_cities); ?>;

            // Clear current options
            citySelect.innerHTML = '<option value="">All</option>';

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

        // Initial load
        window.addEventListener('load', updateStatesAndCities);
    </script>
</body>
</html>