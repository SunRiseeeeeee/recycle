<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - CycleBins</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #0066cc; /* Vibrant blue */
            --primary-light: rgba(0, 102, 204, 0.1);
            --secondary: #004080; /* Darker blue */
            --accent: #ffab00; /* Amber - used sparingly */
            --dark: #263238; /* Dark blue-gray */
            --light: #f5f9ff; /* Very light blue background */
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

        /* Background with blurred image */
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

        /* Top navigation bar */
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

        /* Main content container */
        .main-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 6rem 2rem 4rem; /* Account for top and bottom bars */
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
        
        .form-group input {
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

        /* Bottom footer bar */
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
    <!-- Blurred background image -->
    <div class="background"></div>

    <!-- Top navigation bar with logo -->
    <div class="top-bar">
        <a href="index.php" class="logo">
            <i class="fas fa-recycle"></i>
            <span>CycleBins</span>
        </a>
    </div>

    <!-- Main content -->
    <div class="main-container">
        <div class="admin-container animate__animated animate__fadeInUp">
            <div class="admin-header">
                <h2><i class="fas fa-user-shield"></i> Admin Panel</h2>
                <p>Access the CycleBins administration dashboard</p>
            </div>
            
            <div class="tab-buttons">
                <button class="tab-button active" onclick="showTab('login')">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
                <button class="tab-button" onclick="showTab('signup')">
                    <i class="fas fa-user-plus"></i> Signup
                </button>
            </div>
            
            <div class="form-container">
                <form id="login-form" class="form-wrapper active" action="admin_home.php" method="post">
                    <div class="form-group">
                        <label for="login-username"><i class="fas fa-user"></i> Username</label>
                        <input type="text" id="login-username" name="username" placeholder="Enter your username" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="login-password"><i class="fas fa-lock"></i> Password</label>
                        <input type="password" id="login-password" name="password" placeholder="Enter your password" required>
                    </div>
                    
                    <div id="login-error" class="error-message">
                        <i class="fas fa-exclamation-circle"></i> Invalid username or password
                    </div>
                    
                    <button type="submit" class="submit-btn">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </button>
                </form>
                
                <form id="signup-form" class="form-wrapper" action="admin_home.php" method="post">
                    <div class="form-group">
                        <label for="signup-username"><i class="fas fa-user"></i> Username</label>
                        <input type="text" id="signup-username" name="username" placeholder="Choose a username" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="signup-password"><i class="fas fa-lock"></i> Password</label>
                        <input type="password" id="signup-password" name="password" placeholder="Create a password" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="signup-email"><i class="fas fa-envelope"></i> Email</label>
                        <input type="email" id="signup-email" name="email" placeholder="Your email address" required>
                    </div>
                    
                    <div id="signup-error" class="error-message">
                        <i class="fas fa-exclamation-circle"></i> Username or email already exists
                    </div>
                    
                    <button type="submit" class="submit-btn">
                        <i class="fas fa-user-plus"></i> Create Account
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Bottom footer bar -->
    <div class="footer">
        <div>© 2023 CycleBins. All rights reserved.</div>
        <div class="social-links">
            <a href="#"><i class="fab fa-facebook-f"></i></a>
            <a href="#"><i class="fab fa-twitter"></i></a>
            <a href="#"><i class="fab fa-instagram"></i></a>
        </div>
        <div>
            <a href="#">Privacy Policy</a> | <a href="#">Terms of Service</a>
        </div>
    </div>

    <script>
        function showTab(tab) {
            // Update active tab button
            document.querySelectorAll('.tab-button').forEach(btn => {
                btn.classList.remove('active');
            });
            event.currentTarget.classList.add('active');
            
            // Show the corresponding form
            document.getElementById('login-form').classList.remove('active');
            document.getElementById('signup-form').classList.remove('active');
            document.getElementById(tab + '-form').classList.add('active');
            
            // Clear errors when switching tabs
            document.getElementById('login-error').style.display = 'none';
            document.getElementById('signup-error').style.display = 'none';
        }
    </script>
</body>
</html>