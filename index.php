<?php 
session_start(); 
require 'db.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) { 
    header('Location: login.php'); 
    exit(); 
}

// Fetch user details
$user = [];
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT name, profile_picture FROM users WHERE prn_no = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
}

$page_title = "Alumni Platform - Home";

$is_admin = ($_SESSION['role'] === 'admin');
$is_coordinator = ($_SESSION['role'] === 'coordinator');
$user_department = $_SESSION['department'] ?? '';

// Fetch Pending Verifications (for Admins and Coordinators)
$pending_users = [];
if ($is_admin) {
    $stmt = $pdo->prepare("SELECT prn_no, name, email FROM users WHERE status = 'pending'");
    $stmt->execute();
    $pending_users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} elseif ($is_coordinator) {
    $stmt = $pdo->prepare("SELECT prn_no, name, email FROM users WHERE status = 'pending' AND department = ?");
    $stmt->execute([$user_department]);
    $pending_users = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alumni Platform - Home</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        /* Global Styles */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            color: #333;
            background-color: #f8f9fa;
        }

        /* Header Styles */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: linear-gradient(135deg, #2B2D42 0%, #8D99AE 100%);
            color: white;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            position: relative;
        }

        .header img.logo {
            height: 80px;
            margin-right: 15px;
        }

        .header h1 {
            margin: 0;
            font-size: 28px;
            letter-spacing: 1px;
            animation: fadeIn 1s ease-in;
            flex-grow: 1;
            text-align: center;
        }

        .user-profile {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-left: 20px;
        }

        .user-profile img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #fff;
            margin-bottom: 5px;
        }

        .user-profile span {
            font-size: 14px;
            font-weight: 600;
            color: #fff;
        }

        /* Navigation Links */
        .nav-links {
            display: flex;
            justify-content: center;
            background-color: #ffffff;
            padding: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            flex-wrap: wrap;
            align-items: center;
        }

        .nav-links a, .nav-links button.back-button {
            text-decoration: none;
            color: #2B2D42;
            font-weight: 600;
            padding: 10px 20px;
            border-radius: 25px;
            transition: all 0.3s ease;
            border: 2px solid transparent;
            position: relative;
            overflow: hidden;
            z-index: 1;
            background: none;
            cursor: pointer;
        }

        .nav-links a:hover, .nav-links button.back-button:hover {
            color: white;
            border-color: #2B2D42;
            background-color: #2B2D42;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(43, 45, 66, 0.2);
        }

        .back-button i {
            margin-right: 5px;
        }

        /* Main Content */
        .content {
            flex: 1;
            padding: 40px 20px;
            text-align: center;
            background-color: white;
            margin: 20px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }

        .content h2 {
            color: #2B2D42;
            font-size: 32px;
            margin-bottom: 20px;
            position: relative;
            display: inline-block;
        }

        .content h2::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 0;
            width: 100%;
            height: 3px;
            background: linear-gradient(90deg, transparent, #2B2D42, transparent);
        }

        .content p {
            font-size: 18px;
            line-height: 1.6;
            color: #666;
            margin: 20px 0;
        }

        /* Feature Cards */
        .feature-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            padding: 20px;
            margin-top: 30px;
        }

        .feature-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            cursor: pointer;
            text-decoration: none;
            color: inherit;
            display: block;
            position: relative;
            overflow: hidden;
        }

        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, #2B2D42 0%, #8D99AE 100%);
            opacity: 0;
            transition: opacity 0.3s ease;
            z-index: 1;
            border-radius: 15px;
        }

        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(43, 45, 66, 0.3);
        }

        .feature-card:hover::before {
            opacity: 0.1;
        }

        .feature-card i {
            font-size: 40px;
            color: #2B2D42;
            margin-bottom: 15px;
            position: relative;
            z-index: 2;
        }

        .feature-card h3 {
            color: #333;
            font-size: 20px;
            margin: 15px 0;
            position: relative;
            z-index: 2;
        }

        .feature-card p {
            color: #666;
            font-size: 16px;
            line-height: 1.5;
            position: relative;
            z-index: 2;
        }

        /* Footer Styles */
        footer {
            background: linear-gradient(135deg, #333333 0%, #222222 100%);
            color: white;
            text-align: center;
            padding: 20px 0;
            margin-top: auto;
        }

        footer p {
            margin: 0;
            font-size: 14px;
            opacity: 0.9;
        }

        /* Animation for Header */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .nav-links {
                flex-direction: column;
                align-items: center;
            }

            .nav-links a, .nav-links button.back-button {
                margin: 5px 0;
                width: 80%;
                text-align: center;
            }

            .content {
                margin: 10px;
                padding: 20px 10px;
            }

            .feature-grid {
                grid-template-columns: 1fr;
                padding: 10px;
            }

            .header {
                flex-direction: column;
                text-align: center;
            }

            .header img.logo {
                margin: 0 0 10px 0;
            }

            .user-profile {
                margin: 10px 0 0 0;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="tkiet_new_logo.png" alt="TKIET Logo" class="logo">
        <h1><?php echo htmlspecialchars($page_title); ?></h1>
        <div class="user-profile">
            <?php if (!empty($user['profile_picture']) && file_exists($user['profile_picture'])): ?>
                <img src="<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Profile Picture">
            <?php else: ?>
                <img src="assets/profile-placeholder.png" alt="Profile Placeholder">
            <?php endif; ?>
            <span><?php echo htmlspecialchars($user['name'] ?? 'User'); ?></span>
        </div>
    </div>

    <div class="nav-links">
        <?php if (basename($_SERVER['SCRIPT_NAME']) !== 'index.php'): ?>
            <button class="back-button" onclick="history.back()"><i class="fas fa-arrow-left"></i> Back</button>
        <?php endif; ?>
        <a href="directory.php">Alumni Directory</a>
        <a href="activities.php">Alumni Activities</a>
        <a href="training_and_placement_activities.php">Training and Placement</a>
        <a href="success_stories.php">Success Stories</a>
        <?php if ($is_admin || $is_coordinator): ?>
            <a href="pending_verifications.php" style="color: red;">Pending Verifications (<?= count($pending_users) ?>)</a>
        <?php endif; ?>
        <a href="login.php">Logout</a>
    </div>

    <div class="content">
        <h2>Welcome to the Alumni Platform!</h2>
        <p>Explore the platform to connect with alumni, stay updated on events, and access opportunities.</p>

        <div class="feature-grid">
            <a href="directory.php" class="feature-card">
                <i class="fas fa-user-graduate"></i>
                <h3>Alumni Directory</h3>
                <p>Connect with fellow alumni and expand your network.</p>
            </a>
            <a href="activities.php" class="feature-card">
                <i class="fas fa-calendar-alt"></i>
                <h3>Alumni Activities</h3>
                <p>Stay informed about upcoming events and activities.</p>
            </a>
            <a href="training_and_placement_activities.php" class="feature-card">
                <i class="fas fa-briefcase"></i>
                <h3>Training and Placement</h3>
                <p>Access career development resources and job opportunities.</p>
            </a>
            <a href="success_stories.php" class="feature-card">
                <i class="fas fa-trophy"></i>
                <h3>Success Stories</h3>
                <p>Get inspired by the achievements of our alumni community.</p>
            </a>
        </div>
    </div>

    <footer>
        <p>© <?php echo date("Y"); ?> Alumni Association Platform. All rights reserved.</p>
    </footer>
</body>
</html>