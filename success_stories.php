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

$page_title = "Success Stories";

// Fetch pending users for admin navigation
$is_admin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
$pending_users = [];
if ($is_admin) {
    $stmt = $pdo->prepare("SELECT prn_no, name, email FROM users WHERE status = 'pending'");
    $stmt->execute();
    $pending_users = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Redirect to login if user is not logged in and trying to access submission form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }

    // Handle story submission
    $title = $_POST['title'];
    $content = $_POST['content'];
    $author_id = $_SESSION['user_id'];

    // Debugging: Check if author_id is empty
    if (empty($author_id)) {
        echo "<p style='color: red;'>Error: Author ID (id) is missing.</p>";
        exit();
    }

    // Insert story into the database
    try {
        $stmt = $pdo->prepare("INSERT INTO success_stories (title, content, author_id) VALUES (?, ?, ?)");
        $stmt->execute([$title, $content, $author_id]);
        echo "<p style='color: green; text-align: center;'>Success story submitted for review.</p>";
    } catch (PDOException $e) {
        echo "<p style='color: red; text-align: center;'>Error: " . $e->getMessage() . "</p>";
    }
}

// Fetch approved success stories
$stories = $pdo->query("SELECT * FROM success_stories WHERE status = 'approved'")->fetchAll();

// Determine current page for active state
$current_page = basename($_SERVER['SCRIPT_NAME']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Success Stories - Alumni Association Platform</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        /* General Styling */
        body {
            font-family: 'Raleway', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            margin: 0;
            padding: 0;
            color: #333;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
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
        .container {
            max-width: 1000px;
            margin: 40px auto;
            padding: 40px;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            text-align: center;
            backdrop-filter: blur(10px);
            flex-grow: 1;
        }

        h2, h3 {
            color: #2c3e50;
        }

        h2 {
            font-size: 2.5em;
            margin-bottom: 30px;
        }

        h3 {
            font-size: 1.8em;
            margin-top: 40px;
        }

        /* Form Styling */
        form {
            margin-bottom: 30px;
            text-align: left;
        }

        form input, form textarea, form button {
            width: 100%;
            padding: 12px;
            margin-top: 10px;
            border-radius: 5px;
            border: 1px solid #ddd;
            font-size: 1em;
            font-family: 'Raleway', sans-serif;
        }

        form button {
            background-color: #3498db;
            color: white;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        form button:hover {
            background-color: #2980b9;
        }

        /* Success Stories List */
        .table-container {
            display: grid;
            grid-template-columns: 1fr;
            gap: 30px;
        }

        .story-item {
            background-color: #ffffff;
            border-radius: 15px;
            padding: 25px;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            text-align: left;
        }

        .story-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .story-item h4 {
            color: #2c3e50;
            font-size: 1.4em;
            margin-top: 0;
            margin-bottom: 15px;
        }

        .story-item p {
            color: #7f8c8d;
            line-height: 1.6;
        }

        /* Footer Styles */
        footer {
            text-align: center;
            padding: 20px;
            background-color: #2c3e50;
            color: white;
            margin-top: auto;
        }

        /* Animation for Header */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                padding: 20px;
            }

            h2 {
                font-size: 2em;
            }

            h3 {
                font-size: 1.6em;
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
        <a href="directory.php" class="<?php echo $current_page === 'directory.php' ? 'active' : ''; ?>">Alumni Directory</a>
        <a href="activities.php" class="<?php echo $current_page === 'activities.php' ? 'active' : ''; ?>">Alumni Activities</a>
        <a href="training_and_placement_activities.php" class="<?php echo $current_page === 'training_and_placement_activities.php' ? 'active' : ''; ?>">Training and Placement</a>
        <a href="success_stories.php" class="<?php echo $current_page === 'success_stories.php' ? 'active' : ''; ?>">Success Stories</a>
        <?php if (isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'coordinator'])): ?>
            <a href="pending_verifications.php" class="<?php echo $current_page === 'pending_verifications.php' ? 'active' : ''; ?>" style="color: red;">Pending Verifications (<?= count($pending_users) ?>)</a>
        <?php endif; ?>
        <a href="login.php">Logout</a>
    </div>

    <div class="container">
        <h2>Success Stories</h2>

        <!-- Show Submission Form Only for Logged-In Users -->
        <?php if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])): ?>
            <form action="success_stories.php" method="post">
                <input type="text" name="title" placeholder="Story Title" required>
                <textarea name="content" placeholder="Story Content" rows="5" required></textarea>
                <button type="submit">Submit Story</button>
            </form>
        <?php else: ?>
            <p style="color: red; font-size: 1.2em;">Please <a href="login.php" style="color: #3498db;">log in</a> to submit a success story.</p>
        <?php endif; ?>

        <h3>Approved Stories</h3>
        <div class="table-container">
            <?php if (!empty($stories)): ?>
                <?php foreach ($stories as $story): ?>
                    <div class="story-item">
                        <h4><?php echo htmlspecialchars($story['title']); ?></h4>
                        <p><?php echo nl2br(htmlspecialchars($story['content'])); ?></p>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="color: #7f8c8d; font-size: 1.2em;">No approved success stories yet. Check back later!</p>
            <?php endif; ?>
        </div>
    </div>

    <footer>
        <p>© <?php echo date("Y"); ?> Alumni Association Platform. All Rights Reserved.</p>
    </footer>
</body>
</html>