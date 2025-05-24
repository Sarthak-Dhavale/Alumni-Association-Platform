<?php
session_start();
require 'db.php';

// Fetch user details
$user = [];
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT name, profile_picture FROM users WHERE prn_no = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
}

$page_title = "Alumni Details";

if (!isset($_GET['id'])) {
    header('Location: directory.php');
    exit();
}

$prn_no = $_GET['id'];

$stmt = $pdo->prepare("SELECT * FROM users WHERE prn_no = ?");
$stmt->execute([$prn_no]);
$alumni = $stmt->fetch();

if (!$alumni) {
    echo "Alumni not found.";
    exit();
}

$canEdit = isset($_SESSION['user_id']) && $_SESSION['user_id'] == $alumni['prn_no'];
$is_admin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
$stmt = $pdo->prepare("SELECT prn_no, name, email FROM users WHERE status = 'pending'");
$stmt->execute();
$pending_users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Determine current page for active state
$current_page = basename($_SERVER['SCRIPT_NAME']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alumni Details - Alumni Association Platform</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Segoe+UI:wght@300;400;600&display=swap">
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
            background-color: #8D99AE;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(43, 45, 66, 0.2);
        }

        .nav-links a.active {
            color: white;
            background-color: #2B2D42;
            border-color: #2B2D42;
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

        .alumni-profile {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 20px;
        }

        .alumni-profile img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #2B2D42;
        }

        .alumni-details {
            display: flex;
            flex-direction: column;
        }

        .alumni-details h3 {
            color: #333;
            font-size: 24px;
            margin: 0 0 10px 0;
        }

        .alumni-details p {
            font-size: 16px;
            color: #666;
            margin: 5px 0;
        }

        .button-container {
            margin-top: 20px;
        }

        .back-button, .update-button {
            display: inline-block;
            margin: 0 10px;
            padding: 10px 20px;
            background-color: #2B2D42;
            color: white;
            text-decoration: none;
            border-radius: 25px;
            font-size: 1em;
            border: 2px solid transparent;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .back-button:hover, .update-button:hover {
            color: white;
            border-color: #2B2D42;
            background-color: #8D99AE;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(43, 45, 66, 0.2);
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
            .content {
                margin: 10px;
                padding: 20px 10px;
            }

            .alumni-profile {
                flex-direction: column;
                align-items: center;
                text-align: center;
            }

            .nav-links {
                flex-direction: column;
                align-items: center;
            }

            .nav-links a, .nav-links button.back-button {
                margin: 5px 0;
                width: 80%;
                text-align: center;
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

            .button-container {
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: 10px;
            }

            .back-button, .update-button {
                width: 80%;
                margin: 5px 0;
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
        <a href="directory.php" class="<?php echo $current_page === 'directory.php' || $current_page === 'alumni_details.php' ? 'active' : ''; ?>">Alumni Directory</a>
        <a href="activities.php" class="<?php echo $current_page === 'activities.php' ? 'active' : ''; ?>">Alumni Activities</a>
        <a href="training_and_placement_activities.php" class="<?php echo $current_page === 'training_and_placement_activities.php' ? 'active' : ''; ?>">Training and Placement</a>
        <a href="success_stories.php" class="<?php echo $current_page === 'success_stories.php' ? 'active' : ''; ?>">Success Stories</a>
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin' && $current_page !== 'pending_verifications.php'): ?>
            <a href="pending_verifications.php" class="<?php echo $current_page === 'pending_verifications.php' ? 'active' : ''; ?>" style="color: red;">Pending Verifications (<?= count($pending_users) ?>)</a>
        <?php endif; ?>
        <a href="login.php">Logout</a>
    </div>

    <div class="content">
        <h2>Alumni Details</h2>

        <div class="alumni-profile">
            <?php if (!empty($alumni['profile_picture']) && file_exists($alumni['profile_picture'])): ?>
                <img src="<?php echo htmlspecialchars($alumni['profile_picture']); ?>" alt="Profile Image">
            <?php else: ?>
                <img src="assets/profile-placeholder.png" alt="Profile Image">
            <?php endif; ?>

            <div class="alumni-details">
                <h3><?php echo htmlspecialchars($alumni['name']); ?></h3>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($alumni['email']); ?></p>
                <p><strong>Graduation Year:</strong> <?php echo htmlspecialchars($alumni['ug_graduation_year'] ?? 'N/A'); ?></p>
                <p><strong>PRN No:</strong> <?php echo htmlspecialchars($alumni['prn_no']); ?></p>
                <p><strong>Department:</strong> <?php echo htmlspecialchars($alumni['department'] ?? 'N/A'); ?></p>
                <p><strong>Current Job:</strong> <?php echo htmlspecialchars($alumni['job_profile'] ?? 'N/A'); ?></p>
                <p><strong>Company:</strong> <?php echo htmlspecialchars($alumni['company'] ?? 'N/A'); ?></p>
                <p><strong>Address:</strong> <?php echo htmlspecialchars($alumni['address'] ?? 'N/A'); ?></p>
                <p><strong>Years of Experience:</strong> <?php echo htmlspecialchars($alumni['experience'] ?? 'N/A'); ?></p>
                <p><strong>Skills:</strong> <?php echo htmlspecialchars($alumni['skills'] ?? 'N/A'); ?></p>
            </div>
        </div>

        <div class="button-container">
            <button class="back-button" onclick="window.location.href='directory.php'"><i class="fas fa-arrow-left"></i> Back to Directory</button>
            <?php if ($canEdit): ?>
                <a href="update_profile.php?id=<?php echo $alumni['prn_no']; ?>" class="update-button"><i class="fas fa-edit"></i> Update Profile</a>
            <?php endif; ?>
        </div>
    </div>

    <footer>
        <p>© <?php echo date("Y"); ?> Alumni Association Platform. All rights reserved.</p>
    </footer>
</body>
</html>