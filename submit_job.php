<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
$is_admin = $_SESSION['role'] == 'admin';

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

$page_title = "Submit Job Opportunity";

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

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $conn->real_escape_string($_POST['title']);
    $description = $conn->real_escape_string($_POST['description']);
    $requirements = $conn->real_escape_string($_POST['requirements']);
    $posted_by = $conn->real_escape_string($_SESSION['user_id']);
    $created_at = date('Y-m-d H:i:s');

    $sql = "INSERT INTO training_and_placement (title, description, requirements, posted_by, created_at, approved) 
            VALUES ('$title', '$description', '$requirements', '$posted_by', '$created_at', 0)";

    if ($conn->query($sql) === TRUE) {
        $_SESSION['success_message'] = "New job posted successfully!";
        header("Location: submit_job.php");
        exit();
    } else {
        $error_message = "Error: " . $conn->error;
    }
}
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

        form {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        label {
            font-size: 18px;
            margin: 10px 0;
        }

        input, textarea {
            font-size: 16px;
            padding: 10px;
            width: 80%;
            max-width: 500px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
        }

        input[type="submit"] {
            background-color: #2B2D42;
            color: white;
            border: none;
            cursor: pointer;
            font-size: 18px;
            padding: 15px 30px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        input[type="submit"]:hover {
            background-color: #8D99AE;
        }

        /* Success/Error Message */
        .message {
            padding: 10px;
            margin: 20px;
            border-radius: 5px;
            text-align: center;
        }

        .success {
            background-color: #d4edda;
            color: #155724;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
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
        <h2>Post a New Job</h2>
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="message success"><?php echo htmlspecialchars($_SESSION['success_message']); ?></div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>
        <?php if (isset($error_message)): ?>
            <div class="message error"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>
        <form method="POST" action="submit_job.php">
            <label for="title">Job Title:</label>
            <input type="text" id="title" name="title" required><br><br>

            <label for="description">Job Description:</label>
            <textarea id="description" name="description" required></textarea><br><br>

            <label for="requirements">Job Requirements:</label>
            <textarea id="requirements" name="requirements" required></textarea><br><br>

            <input type="submit" value="Submit Job">
        </form>
    </div>

    <footer>
        <p>&copy; <?php echo date("Y"); ?> Alumni Platform. All rights reserved.</p>
    </footer>
</body>
</html>

<?php
$conn->close();
?>