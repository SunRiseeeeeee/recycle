<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin.php");
    exit();
}
// Placeholder admin data - replace with database queries
$admin_name = "Admin User";
$admin_email = "admin@example.com";
$bin_requests = [
    ["id" => "001", "location" => "Park St.", "details" => "Near the playground, high traffic area", "status" => "Pending"],
    ["id" => "002", "location" => "Main Ave.", "details" => "Near the bus stop, residential area", "status" => "Pending"]
];
$top_users = [
    ["name" => "Sara Brown", "level" => "Eco Champion", "points" => 3200],
    ["name" => "Mohammed Ali", "level" => "Green Warrior", "points" => 2450]
];
// Placeholder recycling locations
$recycling_locations = [
    ["name" => "Park St. Recycling Bin", "location" => "Park St.", "time" => "8 AM - 6 PM", "image" => "bin1.jpg"],
    ["name" => "Main Ave. Green Depot", "location" => "Main Ave.", "time" => "9 AM - 5 PM", "image" => "bin2.jpg"]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - CycleBins</title>
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
        
        .request-list, .user-list {
            list-style: none;
        }
        
        .request-item, .user-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            background: var(--light);
            border-radius: 8px;
            margin-bottom: 0.5rem;
            transition: all 0.3s ease;
        }
        
        .request-item:hover, .user-item:hover {
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
            padding: 2rem;
            border-radius: 15px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
            animation: fadeIn 0.3s ease;
            position: relative;
            background: linear-gradient(135deg, #ffffff 0%, #f0f4f8 100%);
            border: 1px solid rgba(0, 102, 204, 0.2);
        }

        .bin-form h3 {
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
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid var(--gray);
            border-radius: 8px;
            font-size: 1rem;
            background: #ffffff;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }
        
        .form-group input:focus,
        .form-group select:focus,
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
                <div class="user-avatar"><img src="images/admin.jpg" alt="Profile"></div>
                <span class="user-name"><?php echo $admin_name; ?></span>
            </div>
        </div>
    </nav>

    <div class="dashboard-container">
        <aside class="sidebar">
            <ul class="sidebar-menu">
                <li><a href="#dashboard" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="#bin-requests"><i class="fas fa-boxes"></i> Bin Requests</a></li>
                <li><a href="#top-users"><i class="fas fa-users"></i> Top Users</a></li>
                <li><a href="#recycling-locations"><i class="fas fa-map-marker-alt"></i> Recycling Locations</a></li>
                <li><a href="#add-location"><i class="fas fa-plus"></i> Add Location</a></li>
                <li><a href="#settings"><i class="fas fa-cog"></i> Settings</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <!-- Dashboard Section -->
            <section id="dashboard">
                <div class="welcome-banner animate__animated animate__fadeIn">
                    <h2>Welcome back, <?php echo $admin_name; ?>!</h2>
                    <p>Manage CycleBins operations, review requests, and oversee top users from this dashboard.</p>
                </div>
                <div class="stats-cards">
                    <div class="stat-card animate__animated animate__fadeInUp">
                        <h3><i class="fas fa-users"></i> Active Users</h3>
                        <div class="value">150</div>
                    </div>
                    <div class="stat-card animate__animated animate__fadeInUp animate__delay-1s">
                        <h3><i class="fas fa-boxes"></i> Bin Requests</h3>
                        <div class="value"><?php echo count($bin_requests); ?></div>
                    </div>
                    <div class="stat-card animate__animated animate__fadeInUp animate__delay-2s">
                        <h3><i class="fas fa-map-marker-alt"></i> Locations</h3>
                        <div class="value"><?php echo count($recycling_locations); ?></div>
                    </div>
                    <div class="stat-card animate__animated animate__fadeInUp animate__delay-3s">
                        <h3><i class="fas fa-chart-line"></i> Total Points</h3>
                        <div class="value">12,500</div>
                    </div>
                </div>
            </section>

            <!-- Bin Requests Section -->
            <section id="bin-requests">
                <div class="dashboard-card animate__animated animate__fadeIn">
                    <div class="card-header">
                        <h3>Bin Requests</h3>
                        <a href="#" class="see-all">View All <i class="fas fa-chevron-right"></i></a>
                    </div>
                    <ul class="request-list">
                        <?php foreach ($bin_requests as $request): ?>
                        <li class="request-item">
                            <span>Request #<?php echo $request['id']; ?> - Location: <?php echo $request['location']; ?> (<?php echo $request['details']; ?>, <?php echo $request['status']; ?>)</span>
                            <div>
                                <button class="action-btn accept-btn" onclick="showBinForm('<?php echo $request['location']; ?>', '<?php echo $request['id']; ?>')">Accept</button>
                                <button class="action-btn decline-btn" onclick="declineRequest('<?php echo $request['id']; ?>')">Decline</button>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </section>

            <!-- Top Users Section -->
            <section id="top-users">
                <div class="dashboard-card animate__animated animate__fadeIn">
                    <div class="card-header">
                        <h3>Top Users</h3>
                        <a href="#" class="see-all">View All <i class="fas fa-chevron-right"></i></a>
                    </div>
                    <ul class="user-list">
                        <?php foreach ($top_users as $user): ?>
                        <li class="user-item">
                            <span><?php echo $user['name']; ?> (Level: <?php echo $user['level']; ?>)</span>
                            <span><?php echo number_format($user['points']); ?> pts</span>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </section>

            <!-- Recycling Locations Section -->
            <section id="recycling-locations">
                <div class="dashboard-card animate__animated animate__fadeIn">
                    <div class="card-header">
                        <h3>Recycling Locations</h3>
                        <a href="#" class="see-all">View All <i class="fas fa-chevron-right"></i></a>
                    </div>
                    <div class="places-grid" id="locationsGrid">
                        <?php foreach ($recycling_locations as $index => $location): ?>
                        <div class="place-card" data-index="<?php echo $index; ?>">
                            <img src="images/<?php echo $location['image']; ?>" alt="<?php echo $location['name']; ?>" class="place-image">
                            <div class="place-name"><?php echo $location['name']; ?></div>
                            <div class="place-time">Open: <?php echo $location['time']; ?></div>
                            <div class="place-location"><?php echo $location['location']; ?></div>
                            <button class="delete-btn" onclick="deleteLocation(<?php echo $index; ?>)">×</button>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>

            <!-- Add Location Section -->
            <section id="add-location">
                <div class="dashboard-card animate__animated animate__fadeIn">
                    <div class="card-header">
                        <h3>Add Recycling Location</h3>
                    </div>
                    <button class="add-location" onclick="showAddLocationForm()">
                        <i class="fas fa-plus"></i> Add New Location
                    </button>
                </div>
            </section>

            <!-- Settings Section -->
            <section id="settings">
                <div class="dashboard-card animate__animated animate__fadeIn">
                    <div class="card-header">
                        <h3>Settings</h3>
                    </div>
                    <p>Manage admin preferences and system configurations here.</p>
                    <button style="padding: 1rem; background: var(--primary-light); border: none; border-radius: 8px; color: var(--primary); cursor: pointer; transition: all 0.3s ease; width: 100%;">Update Settings</button>
                </div>
            </section>
        </main>
    </div>

    <div class="user-dialog" id="userDialog">
        <div class="profile-image"><img src="images/admin.jpg" alt="Profile"></div>
        <div class="user-info">
            <h3><?php echo $admin_name; ?></h3>
            <p><?php echo $admin_email; ?></p>
        </div>
        <div class="dialog-buttons">
            <button onclick="alert('Switch Account clicked')">Switch Account</button>
            <button onclick="alert('Delete Account clicked')" style="color: #f44336;">Delete Account</button>
            <button onclick="window.location.href='admin.php'">Logout</button>
        </div>
    </div>

    <div class="bin-form-modal" id="binFormModal">
        <div class="bin-form">
            <button class="close-btn" onclick="hideBinForm()">×</button>
            <h3>Add New Bin</h3>
            <form id="binForm" onsubmit="submitBinForm(event)">
                <input type="hidden" id="requestId" name="requestId">
                <div class="form-group">
                    <label for="binLocation">Location:</label>
                    <input type="text" id="binLocation" name="binLocation" required readonly>
                </div>
                <div class="form-group">
                    <label for="binName">Name:</label>
                    <input type="text" id="binName" name="binName" placeholder="e.g., Park St. Recycling Bin" required>
                </div>
                <div class="form-group">
                    <label for="binImage">Image:</label>
                    <input type="file" id="binImage" name="binImage" accept="image/*" required>
                </div>
                <div class="form-group">
                    <label for="openTime">Open Time:</label>
                    <select id="openTime" name="openTime" required>
                        <?php for ($h = 0; $h < 24; $h++): ?>
                            <option value="<?php echo sprintf("%02d:00", $h); ?>"><?php echo sprintf("%02d:00", $h); ?></option>
                            <option value="<?php echo sprintf("%02d:30", $h); ?>"><?php echo sprintf("%02d:30", $h); ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="closeTime">Close Time:</label>
                    <select id="closeTime" name="closeTime" required>
                        <?php for ($h = 0; $h < 24; $h++): ?>
                            <option value="<?php echo sprintf("%02d:00", $h); ?>"><?php echo sprintf("%02d:00", $h); ?></option>
                            <option value="<?php echo sprintf("%02d:30", $h); ?>"><?php echo sprintf("%02d:30", $h); ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="binDetails">Details:</label>
                    <textarea id="binDetails" name="binDetails" placeholder="e.g., Additional notes"></textarea>
                </div>
                <button type="submit" class="submit-btn">Add Bin</button>
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

        // Simulate request actions
        function declineRequest(requestId) {
            alert(`Request #${requestId} declined!`);
            // Add logic to update status in database
        }

        function showBinForm(location, requestId) {
            const modal = document.getElementById('binFormModal');
            const binLocation = document.getElementById('binLocation');
            const requestIdInput = document.getElementById('requestId');
            binLocation.value = location;
            requestIdInput.value = requestId;
            modal.classList.add('active');
        }

        function hideBinForm() {
            const modal = document.getElementById('binFormModal');
            modal.classList.remove('active');
            document.getElementById('binForm').reset();
        }

        function submitBinForm(event) {
            event.preventDefault();
            const requestId = document.getElementById('requestId').value;
            const location = document.getElementById('binLocation').value;
            const name = document.getElementById('binName').value;
            const image = document.getElementById('binImage').files[0];
            const openTime = document.getElementById('openTime').value;
            const closeTime = document.getElementById('closeTime').value;
            const details = document.getElementById('binDetails').value;

            // Simulate submission (for demo purposes)
            const binData = {
                requestId: requestId,
                location: location,
                name: name,
                image: image ? image.name : 'no image',
                time: `${openTime} - ${closeTime}`,
                details: details
            };
            console.log('New Bin Added:', binData);

            // Simulate adding to recycling locations
            const locationsGrid = document.getElementById('locationsGrid');
            const newCard = document.createElement('div');
            newCard.className = 'place-card';
            newCard.setAttribute('data-index', <?php echo count($recycling_locations); ?>);
            newCard.innerHTML = `
                <img src="images/${binData.image}" alt="${binData.name}" class="place-image">
                <div class="place-name">${binData.name}</div>
                <div class="place-time">Open: ${binData.time}</div>
                <div class="place-location">${binData.location}</div>
                <button class="delete-btn" onclick="deleteLocation(${<?php echo count($recycling_locations); ?>})">×</button>
            `;
            locationsGrid.appendChild(newCard);

            // Hide modal and reset
            hideBinForm();
            alert(`Bin added at ${location} with name ${name}!`);
        }

        // Show add location form (placeholder)
        function showAddLocationForm() {
            alert('Add Location form will open here. Implement form logic.');
        }

        function deleteLocation(index) {
            if (confirm('Are you sure you want to delete this location?')) {
                const locationsGrid = document.getElementById('locationsGrid');
                const card = locationsGrid.querySelector(`[data-index="${index}"]`);
                if (card) {
                    card.remove();
                    alert(`Location at index ${index} deleted!`);
                    // Add logic to update database
                }
            }
        }
    </script>
</body>
</html>