<?php
session_start();
// Placeholder user data - replace with database queries
$user_name = "Alex Green";
$user_email = "alex.green@example.com";
$user_points = 1250;
$user_level = "Eco Champion";
$recent_activity = [
    ["action" => "Recycled Plastic", "points" => 50, "date" => "2025-06-15"],
    ["action" => "Recycled Paper", "points" => 30, "date" => "2025-06-14"],
    ["action" => "Recycled Glass", "points" => 40, "date" => "2025-06-12"],
    ["action" => "Recycled Aluminum", "points" => 45, "date" => "2025-06-10"],
    ["action" => "Recycled Cardboard", "points" => 35, "date" => "2025-06-09"],
    ["action" => "Recycled E-Waste", "points" => 60, "date" => "2025-06-08"],
    ["action" => "Recycled Textiles", "points" => 25, "date" => "2025-06-07"]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CycleBins - Activity Log</title>
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

        .main-content {
            padding: 2rem;
            margin-top: 70px;
            max-width: 1200px;
            margin-left: auto;
            margin-right: auto;
            overflow-x: hidden;
        }

        .dashboard-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            margin-bottom: 2rem;
            animation: fadeInUp 0.5s ease;
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .card-header h2 {
            font-size: 1.8rem;
            color: var(--secondary);
            font-weight: 600;
        }

        .back-button {
            background: var(--primary-light);
            border: none;
            border-radius: 8px;
            padding: 0.75rem 1.5rem;
            color: var(--primary);
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
        }

        .back-button:hover {
            background: var(--primary);
            color: white;
        }

        .back-button i {
            font-size: 1rem;
        }

        .activity-list {
            list-style: none;
            width: 100%;
        }

        .activity-item {
            display: flex;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid var(--gray);
            transition: all 0.3s ease;
        }

        .activity-item:hover {
            background: var(--primary-light);
            border-radius: 8px;
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
            font-weight: 500;
            color: var(--dark);
            margin-bottom: 0.25rem;
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
        }

        @media (max-width: 768px) {
            .main-content {
                padding: 1rem;
            }

            .card-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }

            .back-button {
                width: 100%;
            }
        }

        @media (max-width: 480px) {
            .user-dialog {
                width: 250px;
                right: 1rem;
            }

            .card-header h2 {
                font-size: 1.5rem;
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

    <div class="main-content">
        <div class="dashboard-card">
            <div class="card-header">
                <h2>Activity Log</h2>
                <a href="home.php" class="back-button"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
            </div>
            <ul class="activity-list">
                <?php foreach ($recent_activity as $activity): ?>
                <li class="activity-item">
                    <div class="activity-icon">
                        <i class="fas fa-recycle"></i>
                    </div>
                    <div class="activity-details">
                        <div class="activity-action"><?php echo $activity['action']; ?></div>
                        <div class="activity-date"><?php echo $activity['date']; ?></div>
                    </div>
                    <div class="activity-points">+<?php echo $activity['points']; ?> pts</div>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
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
    </script>
</body>
</html>