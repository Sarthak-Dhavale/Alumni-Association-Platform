<?php
session_start();
require 'db.php';
require 'send_email.php'; // Include email sending function

// Restrict access to admins and coordinators
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'coordinator'])) {
    header('Location: login.php');
    exit();
}

// Fetch user details for profile display
$user = [];
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT name, profile_picture FROM users WHERE prn_no = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
}

$page_title = "Pending Verifications";

// Fetch pending users
$is_admin = $_SESSION['role'] === 'admin';
$user_department = $_SESSION['department'] ?? '';
if ($is_admin) {
    $stmt = $pdo->prepare("SELECT prn_no, name, email, profile_picture, department FROM users WHERE status = 'pending'");
    $stmt->execute();
} else {
    $stmt = $pdo->prepare("SELECT prn_no, name, email, profile_picture, department FROM users WHERE status = 'pending' AND department = ?");
    $stmt->execute([$user_department]);
}
$pending_users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle verification actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $prn_no = isset($_POST['prn_no']) ? $_POST['prn_no'] : '';
    $action = isset($_POST['action']) ? $_POST['action'] : '';

    if ($prn_no && in_array($action, ['approve', 'reject'])) {
        // Verify department for coordinators
        $stmt = $pdo->prepare("SELECT department, name, email, phone FROM users WHERE prn_no = ?");
        $stmt->execute([$prn_no]);
        $user_data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user_data) {
            $_SESSION['error'] = "User not found.";
            header('Location: pending_verifications.php');
            exit();
        }

        if ($is_admin || ($user_data['department'] === $user_department)) {
            try {
                if ($action === 'approve') {
                    // Generate password: first 4 letters of name (lowercase) + @ + last 4 digits of phone
                    $name = strtolower($user_data['name']);
                    $name_part = substr(preg_replace('/[^a-z]/', '', $name), 0, 4); // Remove non-letters, take first 4
                    $phone = preg_replace('/[^0-9]/', '', $user_data['phone']); // Remove non-digits
                    $phone_part = substr($phone, -4); // Last 4 digits

                    // Handle edge cases
                    if (strlen($name_part) < 4) {
                        $name_part = str_pad($name_part, 4, 'x'); // Pad with 'x' if name is too short
                    }
                    if (strlen($phone_part) < 4) {
                        $phone_part = str_pad($phone_part, 4, '0', STR_PAD_LEFT); // Pad with '0' if phone is too short
                    }

                    $generated_password = $name_part . '@' . $phone_part;
                    $hashed_password = password_hash($generated_password, PASSWORD_DEFAULT);

                    // Update user status and password
                    $stmt = $pdo->prepare("UPDATE users SET status = 'verified', password = ? WHERE prn_no = ?");
                    if ($stmt->execute([$hashed_password, $prn_no])) {
                        // Send email to alumni with credentials
                        $subject = "TKIET Alumni Platform - Registration Approved";
                        $message = "Dear " . htmlspecialchars($user_data['name']) . ",\n\n";
                        $message .= "Your registration on the TKIET Alumni Platform has been approved!\n\n";
                        $message .= "You can now log in using the following credentials:\n";
                        $message .= "Email: " . $user_data['email'] . "\n";
                        $message .= "Password: " . $generated_password . "\n\n";
                        $message .= "Please log in at: " . $_SERVER['HTTP_HOST'] . "/login.php\n";
                        $message .= "We recommend changing your password after your first login for security.\n\n";
                        $message .= "Best Regards,\n";
                        $message .= "TKIET Alumni Platform Team";

                        if (send_email($user_data['email'], $subject, $message)) {
                            $_SESSION['message'] = "User approved successfully and email sent.";
                        } else {
                            $_SESSION['error'] = "User approved, but email sending failed.";
                            error_log("Failed to send approval email to " . $user_data['email']);
                        }
                    } else {
                        $_SESSION['error'] = "Error updating user status.";
                    }
                } else {
                    // Reject user
                    $stmt = $pdo->prepare("UPDATE users SET status = 'rejected' WHERE prn_no = ?");
                    if ($stmt->execute([$prn_no])) {
                        $_SESSION['message'] = "User rejected successfully.";
                    } else {
                        $_SESSION['error'] = "Error rejecting user.";
                    }
                }
            } catch (PDOException $e) {
                $_SESSION['error'] = "Database error: " . $e->getMessage();
                error_log("Database error in pending_verifications: " . $e->getMessage());
            }
        } else {
            $_SESSION['error'] = "You are not authorized to perform this action.";
        }
        header('Location: pending_verifications.php');
        exit();
    }
}

// Determine current page for active state
$current_page = basename($_SERVER['SCRIPT_NAME']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pending Verifications - Alumni Association Platform</title>
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

        /* Alumni List (Pending Users) */
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

        .action-buttons {
            display: flex;
            gap: 10px;
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
        }

        .action-btn {
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
            font-size: 14px;
        }

        .approve-btn {
            background: #28a745;
        }

        .approve-btn:hover {
            background: #218838;
        }

        .reject-btn {
            background: #dc3545;
        }

        .reject-btn:hover {
            background: #c82333;
        }

        .action-btn i {
            margin-right: 5px;
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

            .action-buttons {
                flex-direction: column;
                gap: 5px;
                right: 10px;
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
            <span><?php echo htmlspecialchars($user['name'] ?? 'Admin'); ?></span>
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
            <a href="pending_verifications.php" class="<?php echo $current_page === 'pending_verifications.php' ? 'active' : ''; ?>" <?php echo $current_page !== 'pending_verifications.php' ? 'style="color: red;"' : ''; ?>>Pending Verifications (<?php echo count($pending_users); ?>)</a>
        <?php endif; ?>
        <a href="login.php">Logout</a>
    </div>

    <div class="content">
        <h2>Pending Verifications</h2>
        <?php if (isset($_SESSION['message'])): ?>
            <p style="color: green;"><?php echo htmlspecialchars($_SESSION['message']); unset($_SESSION['message']); ?></p>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <p style="color: red;"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></p>
        <?php endif; ?>

        <div class="alumni-list">
            <?php if (count($pending_users) > 0): ?>
                <?php foreach ($pending_users as $user): ?>
                    <div class="alumni-card">
                        <a href="alumni_details.php?id=<?php echo $user['prn_no']; ?>" style="text-decoration: none; color: inherit; display: flex; align-items: center; gap: 15px; flex: 1;">
                            <?php if (!empty($user['profile_picture']) && file_exists($user['profile_picture'])): ?>
                                <img src="<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Profile Image">
                            <?php else: ?>
                                <img src="assets/profile-placeholder.png" alt="Profile Placeholder">
                            <?php endif; ?>
                            <div class="alumni-details">
                                <h3><?php echo htmlspecialchars($user['name']); ?></h3>
                                <p><?php echo htmlspecialchars($user['email']); ?></p>
                                <p>Department: <?php echo htmlspecialchars($user['department']); ?></p>
                            </div>
                        </a>
                        <div class="action-buttons">
                            <form method="post" style="display: inline;">
                                <input type="hidden" name="prn_no" value="<?php echo $user['prn_no']; ?>">
                                <input type="hidden" name="action" value="approve">
                                <button type="submit" class="action-btn approve-btn"><i class="fas fa-check"></i> Approve</button>
                            </form>
                            <form method="post" style="display: inline;">
                                <input type="hidden" name="prn_no" value="<?php echo $user['prn_no']; ?>">
                                <input type="hidden" name="action" value="reject">
                                <button type="submit" class="action-btn reject-btn"><i class="fas fa-times"></i> Reject</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No pending verifications found.</p>
            <?php endif; ?>
        </div>
    </div>

    <footer>
        <p>© <?php echo date("Y"); ?> Alumni Association Platform. All rights reserved.</p>
    </footer>
</body>
</html>