<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "alumni_platform";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch user details
$user = [];
if (isset($_SESSION['user_id'])) {
    $user_id = $conn->real_escape_string($_SESSION['user_id']);
    $sql = "SELECT name, profile_picture FROM users WHERE prn_no = '$user_id'";
    $user_result = $conn->query($sql);
    if ($user_result && $user_result->num_rows > 0) {
        $user = $user_result->fetch_assoc();
    }
}

$page_title = "Job Listings";

$is_admin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';

// Fetch pending users for admin navigation
$pending_users = [];
if ($is_admin) {
    $sql = "SELECT prn_no, name, email FROM users WHERE status = 'pending'";
    $result = $conn->query($sql);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $pending_users[] = $row;
        }
    }
}

// Determine current page for active state
$current_page = basename($_SERVER['SCRIPT_NAME']);

// Fetch job listings
$sql = "SELECT * FROM training_and_placement WHERE approved = 0 ORDER BY created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
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

        .job-card {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }

        .job-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            transform: translateY(-5px);
        }

        .job-card h3 {
            font-size: 24px;
            color: #333;
            margin-bottom: 15px;
        }

        .job-card p {
            font-size: 16px;
            line-height: 1.6;
            color: #666;
            margin-bottom: 15px;
        }

        .job-card .details {
            font-size: 14px;
            color: #888;
            margin-bottom: 20px;
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

            .job-card {
                padding: 15px;
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

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 10px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        ::-webkit-scrollbar-thumb {
            background: #2B2D42;
            border-radius: 5px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #8D99AE;
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
        <a href="activities.php" class="<?php echo $current_page === 'activities.php' ? 'active' : ''; ?>">Alumni Activities</a>
        <a href="training_and_placement_activities.php" class="<?php echo $current_page === 'training_and_placement_activities.php' || $current_page ==='job_listings.php'||$current_page === 'submit_job.php' ? 'active' : ''; ?>">Training and Placement</a>
        <a href="success_stories.php" class="<?php echo $current_page === 'success_stories.php' ? 'active' : ''; ?>">Success Stories</a>
        <?php if ($is_admin && $current_page !== 'pending_verifications.php'): ?>
            <a href="pending_verifications.php" class="<?php echo $current_page === 'pending_verifications.php' ? 'active' : ''; ?>" style="color: red;">Pending Verifications (<?= count($pending_users) ?>)</a>
        <?php endif; ?>
        <a href="login.php">Logout</a>
    </div>

    <div class="content">
        <h2>Explore the Latest Job Opportunities</h2>

        <?php if ($result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <div class="job-card">
                    <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                    <p><strong>Description:</strong> <?php echo nl2br(htmlspecialchars($row['description'])); ?></p>
                    <p><strong>Requirements:</strong> <?php echo nl2br(htmlspecialchars($row['requirements'])); ?></p>
                    <p class="details"><strong>Posted On:</strong> <?php echo date('F j, Y, g:i a', strtotime($row['created_at'])); ?></p>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No job listings found.</p>
        <?php endif; ?>
    </div>

    <footer>
        <p>&copy; <?php echo date("Y"); ?> Alumni Association Platform. All rights reserved.</p>
    </footer>
</body>
</html>

<?php
$conn->close();
?>