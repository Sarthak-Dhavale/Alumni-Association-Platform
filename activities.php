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

$page_title = "Alumni Activities";

// Check user role
$is_admin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
$is_coordinator = isset($_SESSION['role']) && $_SESSION['role'] === 'coordinator';
$user_department = $_SESSION['department'] ?? '';

// Delete expired activities and attendance records
$pdo->query("DELETE FROM alumni_attendance WHERE activity_id IN (SELECT id FROM activities WHERE end_date < NOW())");
$pdo->query("DELETE FROM activities WHERE end_date < NOW()");

if ($is_admin) {
    // Admins see all activities
    $activities = $pdo->query("SELECT id, title, poster, department FROM activities")->fetchAll();
    // Fetch alumni attendance details
    $alumni_attendance = $pdo->query("SELECT * FROM alumni_attendance")->fetchAll();
    // Fetch pending users for navigation
    $stmt = $pdo->prepare("SELECT prn_no, name, email FROM users WHERE status = 'pending'");
    $stmt->execute();
    $pending_users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} elseif ($is_coordinator) {
    // Coordinators see only their department's activities
    $stmt = $pdo->prepare("SELECT id, title, poster, department FROM activities WHERE department = ?");
    $stmt->execute([$user_department]);
    $activities = $stmt->fetchAll();
    // Fetch alumni attendance for their department's activities
    $stmt = $pdo->prepare("SELECT aa.* FROM alumni_attendance aa JOIN activities a ON aa.activity_id = a.id WHERE a.department = ?");
    $stmt->execute([$user_department]);
    $alumni_attendance = $stmt->fetchAll();
    // Fetch pending users from their department
    $stmt = $pdo->prepare("SELECT prn_no, name, email FROM users WHERE status = 'pending' AND department = ?");
    $stmt->execute([$user_department]);
    $pending_users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    // Alumni see only upcoming activities
    $activities = $pdo->query("SELECT id, title, poster, department FROM activities WHERE end_date >= NOW()")->fetchAll();
    $pending_users = [];
    $alumni_attendance = [];
}

// Determine current page for active state
$current_page = basename($_SERVER['SCRIPT_NAME']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alumni Activities</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
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

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: linear-gradient(135deg, #2B2D42 0%, #8D99AE 100%);
            color: white;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            /* padding: 10px 20px; */
            /* position: relative; */
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
            /* position: absolute;
            top: 10px;
            right: 20px; */
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

        .nav-links a.active {
            color: white;
            background-color: #2B2D42;
            border-color: #2B2D42;
        }

        .back-button i {
            margin-right: 5px;
        }

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

        .activity-list {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
            list-style: none;
            padding: 0;
        }

        .activity-item {
            background-color: #fff;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #ddd;
            width: 200px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            text-align: center;
        }

        .activity-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(43, 45, 66, 0.3);
        }

        .activity-item img {
            width: 100%;
            height: 150px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid #2B2D42;
        }

        .activity-item h3 {
            font-size: 18px;
            color: #333;
            margin-top: 10px;
        }

        .activity-item a {
            text-decoration: none;
            color: inherit;
        }

        .no-button {
            padding: 10px 15px;
            font-size: 14px;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            margin: 5px;
            transition: background-color 0.3s ease;
            background-color: #e74c3c;
            color: white;
        }

        .no-button:hover {
            background-color: #c0392b;
        }

        .admin-section {
            margin-top: 40px;
            padding: 20px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }

        .admin-section table {
            width: 100%;
            border-collapse: collapse;
        }

        .admin-section th, .admin-section td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: center;
        }

        .admin-section th {
            background-color: #2B2D42;
            color: white;
        }

        .create-activity-btn {
            background-color: #2B2D42;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
            display: inline-block;
        }

        .create-activity-btn:hover {
            background-color: #8D99AE;
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(43, 45, 66, 0.3);
        }

        .tooltip {
            visibility: hidden;
            width: 120px;
            background-color: #2B2D42;
            color: #fff;
            text-align: center;
            padding: 5px 0;
            border-radius: 5px;
            position: absolute;
            bottom: 60px;
            right: 20px;
            opacity: 0;
            transition: opacity 0.3s;
        }

        .create-activity-btn:hover + .tooltip {
            visibility: visible;
            opacity: 1;
        }

        .create-activity-container {
            text-align: center;
            margin-bottom: 20px;
        }

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

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @media (max-width: 768px) {
            .content, .admin-section {
                margin: 10px;
                padding: 20px 10px;
            }

            .activity-list {
                flex-direction: column;
                align-items: center;
            }

            .activity-item {
                width: 80%;
            }

            .header {
                flex-direction: column;
                text-align: center;
                padding: 20px;
            }

            .header img.logo {
                margin: 0 0 10px 0;
            }

            .user-profile {
                position: static;
                margin: 10px 0 0 0;
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

            .create-activity-container {
                text-align: center;
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
        <a href="directory.php" class="<?php echo $current_page === 'directory.php' ? 'active' : ''; ?>">Alumni Directory</a>
        <a href="activities.php" class="<?php echo $current_page === 'activities.php' || $current_page === 'activity_details.php' || $current_page === 'create_activity.php' ? 'active' : ''; ?>">Alumni Activities</a>
        <a href="training_and_placement_activities.php" class="<?php echo $current_page === 'training_and_placement_activities.php' ? 'active' : ''; ?>">Training and Placement</a>
        <a href="success_stories.php" class="<?php echo $current_page === 'success_stories.php' ? 'active' : ''; ?>">Success Stories</a>
        <?php if ($is_admin || $is_coordinator): ?>
            <a href="pending_verifications.php" class="<?php echo $current_page === 'pending_verifications.php' ? 'active' : ''; ?>" style="color: red;">Pending Verifications (<?= count($pending_users) ?>)</a>
        <?php endif; ?>
        <a href="login.php">Logout</a>
    </div>

    <div class="content">
        <?php if (isset($_GET['success'])): ?>
            <p style="color: green;">Activity deleted successfully</p>
        <?php endif; ?>
        <?php if (isset($_GET['error'])): ?>
            <p style="color: red;"><?php echo htmlspecialchars($_GET['error']); ?></p>
        <?php endif; ?>
        
        <h2>Upcoming Alumni Activities</h2>

        <?php if ($is_admin || $is_coordinator): ?>
            <div class="create-activity-container">
                <button class="create-activity-btn" onclick="window.location.href='create_activity.php'">Create New Activity</button>
            </div>
        <?php endif; ?>

        <?php if (empty($activities)): ?>
            <p>There are no upcoming events.</p>
        <?php else: ?>
            <ul class="activity-list">
                <?php foreach ($activities as $activity): ?>
                    <li class="activity-item">
                        <a href="activity_details.php?id=<?php echo $activity['id']; ?>">
                            <?php if (!empty($activity['poster'])): ?>
                                <img src="data:image/jpeg;base64,<?php echo base64_encode($activity['poster']); ?>" alt="Activity Poster">
                            <?php else: ?>
                                <img src="assets/profile-placeholder.png" alt="Activity Placeholder">
                            <?php endif; ?>
                            <h3><?php echo htmlspecialchars($activity['title']); ?></h3>
                        </a>
                        <?php if ($is_admin || ($is_coordinator && $activity['department'] === $user_department)): ?>
                            <form method="post" action="delete_activity.php" onsubmit="return confirm('Are you sure you want to delete this activity?');">
                                <input type="hidden" name="id" value="<?php echo $activity['id']; ?>">
                                <button type="submit" class="no-button">Delete</button>
                            </form>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>

    <?php if ($is_admin || $is_coordinator): ?>
        <div class="admin-section">
            <h2>Alumni Attendance Records</h2>
            <table>
                <tr>
                    <th>Name</th>
                    <th>Address</th>
                    <th>People Count</th>
                    <th>Stay Days</th>
                </tr>
                <?php foreach ($alumni_attendance as $attendance): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($attendance['alumni_name']); ?></td>
                        <td><?php echo htmlspecialchars($attendance['address']); ?></td>
                        <td><?php echo htmlspecialchars($attendance['people_count']); ?></td>
                        <td><?php echo htmlspecialchars($attendance['stay_days']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
    <?php endif; ?>

    <footer>
        <p>© <?php echo date("Y"); ?> Alumni Association Platform. All rights reserved.</p>
    </footer>
</body>
</html>