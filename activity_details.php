<?php
// activity_details.php
session_start();
require 'db.php';

// Fetch user details
$user = [];
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT name, profile_picture FROM users WHERE prn_no = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
}

$page_title = "Activity Details";

// Check if admin is logged in
$is_admin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';

// Fetch pending users for navigation (admin only)
$pending_users = [];
if ($is_admin) {
    $stmt = $pdo->prepare("SELECT prn_no, name, email FROM users WHERE status = 'pending'");
    $stmt->execute();
    $pending_users = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Determine current page for active state
$current_page = basename($_SERVER['SCRIPT_NAME']);

$id = isset($_GET['id']) ? (int)$_GET['id'] : null;

if (!$id) {
    die("Invalid activity selected.");
}

$stmt = $pdo->prepare("SELECT * FROM activities WHERE id = ?");
$stmt->execute([$id]);
$activity = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$activity) {
    die("Activity not found.");
}

// Fetch existing attendance for this user and activity (if any)
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
$attendance_record = null;
if ($user_id && !$is_admin) {
    $stmt = $pdo->prepare("SELECT people_count, stay_days FROM alumni_attendance WHERE activity_id = ? AND user_id = ?");
    $stmt->execute([$id, $user_id]);
    $attendance_record = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($activity['title']); ?> - Alumni Association</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        /* Styles copied from activities.php */
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

        .activity-details img {
            width: 400px;
            height: auto;
            max-height: 400px;
            object-fit: cover;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        p {
            font-size: 16px;
            color: #555;
            margin: 10px 0;
        }

        .polling {
            margin-top: 20px;
        }

        .polling button {
            padding: 10px 20px;
            font-size: 16px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .polling button:disabled {
            background-color: #cccccc;
            cursor: not-allowed;
        }

        .polling button:hover:not(:disabled) {
            background-color: #0056b3;
        }

        .attendance-message {
            color: #28a745;
            font-weight: 600;
            margin-top: 10px;
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
            .content {
                margin: 10px;
                padding: 20px 10px;
            }

            .activity-details img {
                width: 100%;
                max-width: 300px;
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

            .nav-links {
                flex-direction: column;
                align-items: center;
            }

            .nav-links a, .nav-links button.back-button {
                margin: 5px 0;
                width: 80%;
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
        <a href="activities.php" class="<?php echo $current_page === 'activities.php' || $current_page === 'activity_details.php' ? 'active' : ''; ?>">Alumni Activities</a>
        <a href="training_and_placement_activities.php" class="<?php echo $current_page === 'training_and_placement_activities.php' ? 'active' : ''; ?>">Training and Placement</a>
        <a href="success_stories.php" class="<?php echo $current_page === 'success_stories.php' ? 'active' : ''; ?>">Success Stories</a>
        <?php if ($is_admin && $current_page !== 'pending_verifications.php'): ?>
            <a href="pending_verifications.php" class="<?php echo $current_page === 'pending_verifications.php' ? 'active' : ''; ?>" <?php echo $current_page !== 'pending_verifications.php' ? 'style="color: red;"' : ''; ?>>Pending Verifications (<?= count($pending_users) ?>)</a>
        <?php endif; ?>
        <a href="login.php">Logout</a>
    </div>

    <div class="content">
        <h2><?php echo htmlspecialchars($activity['title']); ?></h2>
        <div class="activity-details">
            <?php if (!empty($activity['poster'])): ?>
                <img src="data:image/jpeg;base64,<?php echo base64_encode($activity['poster']); ?>" alt="Activity Poster">
            <?php else: ?>
                <img src="assets/profile-placeholder.png" alt="Activity Placeholder">
            <?php endif; ?>
            <p><strong>Start Date:</strong> <?php echo htmlspecialchars($activity['start_date']); ?></p>
            <p><strong>End Date:</strong> <?php echo htmlspecialchars($activity['end_date']); ?></p>
            <p><strong>Location:</strong> <?php echo htmlspecialchars($activity['location']); ?></p>
            <p><strong>Description:</strong> <?php echo htmlspecialchars($activity['description']); ?></p>

            <?php if (!$is_admin && $user_id): ?>
            <div class="polling">
                <p><strong>Are you attending?</strong></p>
                <?php if ($attendance_record): ?>
                    <button disabled>Yes, I’m coming</button>
                    <p class="attendance-message">You have already responded: Attending with <?php echo htmlspecialchars($attendance_record['people_count']); ?> people for <?php echo htmlspecialchars($attendance_record['stay_days']); ?> days.</p>
                <?php else: ?>
                    <button onclick="attendActivity()">Yes, I’m coming</button>
                <?php endif; ?>
            </div>

            <script>
                function attendActivity() {
                    let numPeople = prompt('How many people are coming with you (including yourself)?');
                    numPeople = parseInt(numPeople);
                    if (!numPeople || isNaN(numPeople) || numPeople < 1) {
                        alert('Please enter a valid number of people.');
                        return;
                    }

                    let stayDays = prompt('How many days will you stay?');
                    stayDays = parseInt(stayDays);
                    if (isNaN(stayDays) || stayDays < 0) {
                        alert('Please enter a valid number of stay days.');
                        return;
                    }

                    // Prepare data
                    const data = {
                        activity_id: <?php echo (int)$activity['id']; ?>,
                        people_count: numPeople,
                        stay_days: stayDays
                    };

                    // Send to attend_activity.php
                    fetch('attend_activity.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(data)
                    }).then(response => response.text())
                    .then(response => {
                        alert(response);
                        location.reload();  // Refresh to reflect updated attendance
                    }).catch(error => {
                        console.error('Error:', error);
                        alert('Something went wrong!');
                    });
                }
            </script>

            <?php endif; ?>
        </div>
    </div>

    <footer>
        <p>© <?php echo date("Y"); ?> Alumni Association Platform. All rights reserved.</p>
    </footer>
</body>
</html>