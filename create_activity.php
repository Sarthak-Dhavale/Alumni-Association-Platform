<?php
session_start();
require 'db.php';

// Fetch user details and validate session
$user = [];
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT name, profile_picture FROM users WHERE prn_no = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        // If user is not found, redirect to login
        header('Location: login.php');
        exit;
    }
} else {
    header('Location: login.php');
    exit;
}

$page_title = "Create New Activity";

$is_admin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
$stmt = $pdo->prepare("SELECT prn_no, name, email FROM users WHERE status = 'pending'");
$stmt->execute();
$pending_users = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $location = $_POST['location'];
    $description = $_POST['description'];
    $batch = $_POST['batch'];
    $department = $_POST['department'];

    // Get the current user's prn_no from the session
    $created_by = $_SESSION['user_id'];

    if (isset($_FILES['poster']) && $_FILES['poster']['error'] === UPLOAD_ERR_OK) {
        $poster = file_get_contents($_FILES['poster']['tmp_name']);
    } else {
        $poster = null; 
    }

    try {
        // Include created_by in the INSERT query
        $sql = "INSERT INTO activities (title, start_date, end_date, location, description, batch, department, poster, created_by) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$title, $start_date, $end_date, $location, $description, $batch, $department, $poster, $created_by]);

        header("Location: activities.php");
        exit;
    } catch (Exception $e) {
        die("Database Error: " . $e->getMessage());
    }
}

// Determine current page for active state
$current_page = basename($_SERVER['SCRIPT_NAME']);
// Debug: Fallback to REQUEST_URI if SCRIPT_NAME is unreliable
if ($current_page === 'index.php' || empty($current_page)) {
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $current_page = basename($uri);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New Activity</title>
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

        .form-container {
            max-width: 450px;
            margin: 0 auto;
        }

        .form-container label {
            font-weight: 600;
            color: #2B2D42;
            margin-bottom: 5px;
            display: block;
        }

        .form-container input, 
        .form-container select, 
        .form-container textarea {
            width: 100%;
            padding: 12px;
            margin: 8px 0 15px 0;
            border: 2px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
            box-sizing: border-box;
            background-color: #f9f9f9;
        }

        .form-container input:focus, 
        .form-container select:focus, 
        .form-container textarea:focus {
            border-color: #2B2D42;
            outline: none;
        }

        .form-container textarea {
            resize: vertical;
            min-height: 100px;
        }

        .form-container button {
            padding: 12px 20px;
            background-color: #2B2D42;
            color: white;
            border: none;
            border-radius: 6px;
            width: 100%;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
        }

        .form-container button:hover {
            background-color: #8D99AE;
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

            .form-container {
                width: 90%;
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
    <!-- Debug: Display current page for troubleshooting -->
    <div style="display: none;">Current Page: <?php echo htmlspecialchars($current_page); ?></div>

    <div class="header">
        <img src="tkiet_new_logo.png" alt="TKIET Logo" class="logo">
        <h1><?php echo htmlspecialchars($page_title); ?></h1>
        <div class="user-profile">
            <?php if (!empty($user['profile_picture'])): ?>
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
        <?php if ($is_admin && $current_page !== 'pending_verifications.php'): ?>
            <a href="pending_verifications.php" class="<?php echo $current_page === 'pending_verifications.php' ? 'active' : ''; ?>" style="color: red;">Pending Verifications (<?= count($pending_users) ?>)</a>
        <?php endif; ?>
        <a href="login.php">Logout</a>
    </div>

    <div class="content">
        <h2>Create New Activity</h2>
        <div class="form-container">
            <form action="create_activity.php" method="POST" enctype="multipart/form-data">
                <label for="title">Activity Title</label>
                <input type="text" name="title" id="title" required>

                <label for="start_date">Start Date</label>
                <input type="date" name="start_date" id="轿车" required>

                <label for="end_date">End Date</label>
                <input type="date" name="end_date" id="end_date" required>

                <label for="location">Location</label>
                <input type="text" name="location" id="location" required>

                <label for="description">Description</label>
                <textarea name="description" id="description" rows="4" required></textarea>

                <label for="batch">Batch</label>
                <input type="text" name="batch" id="batch" placeholder="e.g., 2021, 2020-2021" required>

                <label for="department">Department</label>
                <select name="department" id="department" required>
                    <option value="CSE">Computer Science</option>
                    <option value="ECE">Electronics & Telecommunication</option>
                    <option value="MECH">Mechanical Engineering</option>
                    <option value="CIVIL">Civil Engineering</option>
                    <option value="CHEM">Chemical Engineering</option>
                    <option value="ELE">Electrical Engineering</option>
                </select>

                <label for="poster">Activity Poster</label>
                <input type="file" name="poster" id="poster" accept="image/*">

                <button type="submit">Create Activity</button>
            </form>
        </div>
    </div>

    <footer>
        <p>© <?php echo date("Y"); ?> Alumni Association Platform. All rights reserved.</p>
    </footer>
</body>
</html>