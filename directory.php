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

$page_title = "Alumni Directory";

// Pagination setup
$resultsPerPage = 5; // Number of records per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $resultsPerPage;

// Fetch pending users for admin navigation
$is_admin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
$pending_users = [];
if ($is_admin) {
    $stmt = $pdo->prepare("SELECT prn_no, name, email FROM users WHERE status = 'pending'");
    $stmt->execute();
    $pending_users = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Search and pagination
$searchQuery = "";
if (isset($_POST['search'])) {
    $searchQuery = htmlspecialchars($_POST['search']);
    $stmt = $pdo->prepare("SELECT * FROM users WHERE (name LIKE ? OR email LIKE ? OR degree LIKE ?) AND status = 'verified' AND role = 'user' LIMIT $start, $resultsPerPage");
    $stmt->execute(['%' . $searchQuery . '%', '%' . $searchQuery . '%', '%' . $searchQuery . '%']);
    $alumni = $stmt->fetchAll();

    // Get total number of search results for pagination
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE (name LIKE ? OR email LIKE ? OR degree LIKE ?) AND status = 'verified' AND role = 'user'");
    $countStmt->execute(['%' . $searchQuery . '%', '%' . $searchQuery . '%', '%' . $searchQuery . '%']);
    $totalResults = $countStmt->fetchColumn();
} else {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE status = 'verified' AND role = 'user' LIMIT $start, $resultsPerPage");
    $stmt->execute();
    $alumni = $stmt->fetchAll();

    // Get total number of records for pagination
    $totalResults = $pdo->query("SELECT COUNT(*) FROM users WHERE status = 'verified' AND role = 'user'")->fetchColumn();
}

// Calculate total pages
$totalPages = ceil($totalResults / $resultsPerPage);

// Handle delete alumni
if (isset($_GET['remove']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    $removeId = $_GET['remove']; // keep as string for PRN

    // Ensure the ID exists before attempting deletion
    $stmt = $pdo->prepare("DELETE FROM users WHERE prn_no = ?");
    if ($stmt->execute([$removeId])) {
        $_SESSION['message'] = "Alumni removed successfully.";
    } else {
        $_SESSION['error'] = "Error deleting alumni.";
    }

    // Redirect to avoid reload issues
    header('Location: directory.php');
    exit();
}

// Determine current page for active state
$current_page = basename($_SERVER['SCRIPT_NAME']);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alumni Directory - Alumni Association Platform</title>
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

        /* Search Form */
        form {
            display: flex;
            justify-content: center;
            margin-bottom: 30px;
            gap: 10px;
        }

        form input[type="text"] {
            padding: 10px;
            font-size: 1em;
            border: 1px solid #ddd;
            border-radius: 5px;
            width: 300px;
            outline: none;
            transition: border-color 0.3s ease;
        }

        form input[type="text"]:focus {
            border-color: #2B2D42;
        }

        form button {
            padding: 10px 20px;
            background-color: #2B2D42;
            color: white;
            border: none;
            font-size: 1em;
            cursor: pointer;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        form button:hover {
            background-color: #8D99AE;
        }

        /* Alumni List */
        .alumni-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            padding: 20px;
        }

        .alumni-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .alumni-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(43, 45, 66, 0.3);
        }

        .alumni-card img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #2B2D42;
        }

        .alumni-details {
            flex: 1;
            text-align: left;
        }

        .alumni-details h3 {
            margin: 0;
            font-size: 20px;
            color: #333;
        }

        .alumni-details p {
            color: #666;
            font-size: 16px;
            margin: 5px 0;
        }

        .remove-btn {
            background: #e74c3c;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
        }

        .remove-btn:hover {
            background-color: #c0392b;
        }

        .remove-btn i {
            margin-right: 8px;
        }

        /* Add Alumni Button */
        .add-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background-color: #2B2D42;
            color: white;
            border: none;
            padding: 15px;
            border-radius: 50%;
            font-size: 24px;
            cursor: pointer;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
        }

        .add-btn:hover {
            background-color: #8D99AE;
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(43, 45, 66, 0.3);
        }

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 20px;
            gap: 10px;
        }

        .pagination a {
            padding: 10px 15px;
            background-color: #2B2D42;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 1em;
            transition: background-color 0.3s ease;
        }

        .pagination a:hover {
            background-color: #8D99AE;
        }

        .pagination a.active {
            background-color: #1a252f;
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
            form input[type="text"] {
                width: 200px;
            }

            .alumni-list {
                grid-template-columns: 1fr;
                padding: 10px;
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
        <?php if (isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'coordinator'])): ?>
            <a href="pending_verifications.php" class="<?php echo $current_page === 'pending_verifications.php' ? 'active' : ''; ?>" style="color: red;">Pending Verifications (<?= count($pending_users) ?>)</a>
        <?php endif; ?>
        <a href="login.php">Logout</a>
    </div>

    <div class="content">
        <h2>Alumni Directory</h2>
        
        <form method="post" action="directory.php">
            <input type="text" name="search" placeholder="Search by name, email" value="<?php echo htmlspecialchars($searchQuery); ?>" />
            <button type="submit">Search</button>
        </form>

        <div class="alumni-list">
            <?php if (count($alumni) > 0): ?>
                <?php foreach ($alumni as $alum): ?>
                    <div class="alumni-card">
                        <a href="alumni_details.php?id=<?php echo $alum['prn_no']; ?>" style="text-decoration: none; color: inherit; display: flex; align-items: center; gap: 15px; flex: 1;">
                            <?php if (!empty($alum['profile_picture']) && file_exists($alum['profile_picture'])): ?>
                                <img src="<?php echo htmlspecialchars($alum['profile_picture']); ?>" alt="Profile Image">
                            <?php else: ?>
                                <img src="assets/profile-placeholder.png" alt="Profile Placeholder">
                            <?php endif; ?>
                            <div class="alumni-details">
                                <h3><?php echo htmlspecialchars($alum['name']); ?></h3>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No alumni found.</p>
            <?php endif; ?>
        </div>

        <div class="pagination">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="directory.php?page=<?php echo $i; ?>" class="<?php echo $i == $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
            <?php endfor; ?>
        </div>
    </div>

    <!-- Add Alumni Button for Admin -->
    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
        <a href="register.php">
            <button class="add-btn"><i class="fas fa-user-plus"></i></button>
        </a>
    <?php endif; ?>

    <footer>
        <p>© <?php echo date("Y"); ?> Alumni Association Platform. All rights reserved.</p>
    </footer>
</body>
</html>