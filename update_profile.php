<?php
// update_profile.php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if (!isset($_GET['id'])) {
    header('Location: directory.php');
    exit();
}

$prn_no = $_GET['id'];

// Ensure the logged-in user is updating their own profile
if ($_SESSION['user_id'] != $prn_no) {
    header('Location: directory.php');
    exit();
}

// Function to sanitize input
function sanitize_input($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Fetch user details
$stmt = $pdo->prepare("SELECT * FROM users WHERE prn_no = ?");
$stmt->execute([$prn_no]);
$alumni = $stmt->fetch();

if (!$alumni) {
    echo "Alumni not found.";
    exit();
}

// Fetch pending users for admin navigation
$is_admin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
$stmt = $pdo->prepare("SELECT prn_no, name, email FROM users WHERE status = 'pending'");
$stmt->execute();
$pending_users = $stmt->fetchAll(PDO::FETCH_ASSOC);

$errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Personal Details
    $full_name = sanitize_input($_POST['full_name']);
    $dob = sanitize_input($_POST['dob']);
    $gender = sanitize_input($_POST['gender']);
    $address = sanitize_input($_POST['address']);

    // Contact Details
    $email = sanitize_input($_POST['email']);
    $phone = sanitize_input($_POST['phone']);
    $linkedin = sanitize_input($_POST['linkedin']);
    $github = sanitize_input($_POST['github']);

    // Educational Details
    $ug_degree = sanitize_input($_POST['ug_degree']);
    $ug_institute = sanitize_input($_POST['ug_institute']);
    $ug_graduation_year = sanitize_input($_POST['ug_graduation_year']);
    $pg_degree = sanitize_input($_POST['pg_degree']);
    $pg_institute = sanitize_input($_POST['pg_institute']);
    $pg_graduation_year = sanitize_input($_POST['pg_graduation_year']);
    $department = sanitize_input($_POST['department']);
    $prn_no = sanitize_input($_POST['prn_no']);

    // Company Details
    $company = sanitize_input($_POST['company']);
    $experience = sanitize_input($_POST['experience']);
    $position = sanitize_input($_POST['position']);
    $skills = sanitize_input($_POST['skills']);

    // Other Information
    $emergency_contact = sanitize_input($_POST['emergency_contact']);
    $job_profile = sanitize_input($_POST['job_profile']);

    // Profile picture handling
    $target_file = $alumni['profile_picture'];
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $profile_picture = $_FILES['profile_picture'];
        $target_dir = "Uploads/";
        $file_extension = strtolower(pathinfo($_FILES["profile_picture"]["name"], PATHINFO_EXTENSION));
        $new_filename = uniqid() . '.' . $file_extension;
        $target_file = $target_dir . $new_filename;

        // Validate image
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        $max_size = 50000000; // 500KB

        if (!in_array($file_extension, $allowed_types)) {
            $errors[] = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
        }

        if ($_FILES["profile_picture"]["size"] > $max_size) {
            $errors[] = "Sorry, your file must be less than 500KB.";
        }

        if (empty($errors) && !move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
            $errors[] = "Sorry, there was an error uploading your file.";
            $target_file = $alumni['profile_picture'];
        }
    }

    // Update database if no errors
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("UPDATE users SET 
                name = ?, dob = ?, gender = ?, address = ?,
                email = ?, phone = ?, linkedin = ?, github = ?,
                ug_degree = ?, ug_institute = ?, ug_graduation_year = ?,
                pg_degree = ?, pg_institute = ?, pg_graduation_year = ?,
                department = ?, prn_no = ?, company = ?, experience = ?, position = ?, skills = ?,
                emergency_contact = ?, job_profile = ?, profile_picture = ?
                WHERE prn_no = ?");
            $stmt->execute([
                $full_name, $dob, $gender, $address,
                $email, $phone, $linkedin, $github,
                $ug_degree, $ug_institute, $ug_graduation_year,
                $pg_degree, $pg_institute, $pg_graduation_year,
                $department, $prn_no, $company, $experience, $position, $skills,
                $emergency_contact, $job_profile, $target_file,
                $prn_no
            ]);

            header('Location: alumni_details.php?id=' . $prn_no);
            exit();
        } catch (PDOException $e) {
            $errors[] = "Database Error: " . $e->getMessage();
        }
    }
}

// Determine current page for active state
$current_page = basename($_SERVER['SCRIPT_NAME']);
$page_title = "Update Profile";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Profile</title>
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

        .content {
            flex: 1;
            padding: 30px 20px;
            /* text-align: center; */
            background-color: white;
            margin: 20px auto;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            max-width: 800px; /* Reduced width of the content box */
        }
        .content .center{
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .content h2 {
            color: #2B2D42;
            ali
            font-size: 28px; /* Slightly smaller heading */
            margin-bottom: 15px;
            position: relative;
            display: inline-block;
        }

        .content h2::after {
            content: '';
            position: absolute;
            bottom: -8px;
            left: 0;
            width: 100%;
            height: 3px;
            background: linear-gradient(90deg, transparent, #2B2D42, transparent);
        }

        .section {
            margin-bottom: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
        }

        h3 {
            color: #2B2D42;
            margin-bottom: 20px;
            padding-bottom: 8px;
            border-bottom: 2px solid #2B2D42;
            font-size: 20px; /* Slightly smaller section heading */
        }

        .form-group {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px; /* Reduced gap between form elements */
            margin-bottom: 20px;
        }

        .form-group.full-width {
            grid-template-columns: 1fr;
        }

        .form-group > div {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        label {
            display: block;
            margin-bottom: 4px;
            font-weight: 600;
            color: #2B2D42;
            font-size: 14px; /* Smaller label font */
        }

        input[type="text"],
        input[type="email"],
        input[type="tel"],
        input[type="url"],
        input[type="date"],
        input[type="number"],
        select,
        textarea {
            width: 100%;
            padding: 10px;
            border: 2px solid #ddd;
            border-radius: 6px;
            font-size: 14px; /* Smaller input font */
            box-sizing: border-box;
            background-color: #f9f9f9;
        }

        input:focus,
        select:focus,
        textarea:focus {
            border-color: #2B2D42;
            outline: none;
        }

        textarea {
            height: 80px; /* Reduced textarea height */
            resize: vertical;
        }

        .Update {
            background-color: #2B2D42;
            color: white;
            padding: 10px 18px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            width: 100%;
            font-size: 14px; /* Smaller button font */
            font-weight: 600;
            margin-top: 15px;
        }

        button:hover {
            background-color: #8D99AE;
        }

        .error {
            color: #e74c3c;
            margin-bottom: 15px;
            text-align: left;
            font-size: 14px;
        }

        #profile_preview {
            max-width: 150px; /* Smaller profile picture preview */
            max-height: 150px;
            margin-top: 8px;
            display: <?php echo $alumni['profile_picture'] ? 'block' : 'none'; ?>;
        }

        footer {
            background: linear-gradient(135deg, #333333 0%, #222222 100%);
            color: white;
            text-align: center;
            padding: 15px 0;
            margin-top: auto;
        }

        footer p {
            margin: 0;
            font-size: 12px; /* Smaller footer font */
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
                max-width: 100%; /* Ensure full width on smaller screens */
            }

            .form-group {
                grid-template-columns: 1fr;
                gap: 15px;
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
            <?php if (!empty($alumni['profile_picture']) && file_exists($alumni['profile_picture'])): ?>
                <img src="<?php echo htmlspecialchars($alumni['profile_picture']); ?>" alt="Profile Picture">
            <?php else: ?>
                <img src="assets/profile-placeholder.png" alt="Profile Placeholder">
            <?php endif; ?>
            <span><?php echo htmlspecialchars($alumni['name'] ?? 'User'); ?></span>
        </div>
    </div>

    <div class="nav-links">
        <?php if (basename($_SERVER['SCRIPT_NAME']) !== 'index.php'): ?>
            <button class="back-button" onclick="history.back()"><i class="fas fa-arrow-left"></i> Back</button>
        <?php endif; ?>
        <a href="directory.php" class="<?php echo $current_page === 'directory.php' || $current_page === 'alumni_details.php' || $current_page === 'update_profile.php' ? 'active' : ''; ?>">Alumni Directory</a>
        <a href="activities.php" class="<?php echo $current_page === 'activities.php' ? 'active' : ''; ?>">Alumni Activities</a>
        <a href="training_and_placement_activities.php" class="<?php echo $current_page === 'training_and_placement_activities.php' ? 'active' : ''; ?>">Training and Placement</a>
        <a href="success_stories.php" class="<?php echo $current_page === 'success_stories.php' ? 'active' : ''; ?>">Success Stories</a>
        <?php if ($is_admin && $current_page !== 'pending_verifications.php'): ?>
            <a href="pending_verifications.php" class="<?php echo $current_page === 'pending_verifications.php' ? 'active' : ''; ?>" style="color: red;">Pending Verifications (<?= count($pending_users) ?>)</a>
        <?php endif; ?>
        <a href="login.php">Logout</a>
    </div>

    <div class="content">
        <div class="center">
            <h2>Update Profile</h2>
        </div>


        <?php if (!empty($errors)): ?>
            <div class="error">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo $error; ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="post" action="update_profile.php?id=<?php echo htmlspecialchars($prn_no); ?>" enctype="multipart/form-data">
            <!-- Personal Details Section -->
            <div class="section">
                <h3>Personal Details</h3>
                <div class="form-group">
                    <div>
                        <label>Full Name:</label>
                        <input type="text" name="full_name" value="<?php echo htmlspecialchars($alumni['name']); ?>" required>
                    </div>
                    <div>
                        <label>Date of Birth:</label>
                        <input type="date" name="dob" value="<?php echo htmlspecialchars($alumni['dob']); ?>" required>
                    </div>
                    <div>
                        <label>Gender:</label>
                        <select name="gender" required>
                            <option value="Male" <?php echo $alumni['gender'] === 'Male' ? 'selected' : ''; ?>>Male</option>
                            <option value="Female" <?php echo $alumni['gender'] === 'Female' ? 'selected' : ''; ?>>Female</option>
                            <option value="Other" <?php echo $alumni['gender'] === 'Other' ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                </div>
                <div class="form-group full-width">
                    <div>
                        <label>Address:</label>
                        <textarea name="address" required><?php echo htmlspecialchars($alumni['address']); ?></textarea>
                    </div>
                </div>
                <div class="form-group">
                    <div>
                        <label>Profile Picture:</label>
                        <input type="file" name="profile_picture" accept="image/*" onchange="previewImage(event)">
                        <img id="profile_preview" src="<?php echo htmlspecialchars($alumni['profile_picture'] ?? ''); ?>" alt="Profile Picture Preview">
                    </div>
                </div>
            </div>

            <!-- Contact Details Section -->
            <div class="section">
                <h3>Contact Details</h3>
                <div class="form-group">
                    <div>
                        <label>Email:</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($alumni['email']); ?>" required>
                    </div>
                    <div>
                        <label>Phone Number:</label>
                        <input type="tel" name="phone" value="<?php echo htmlspecialchars($alumni['phone']); ?>" required>
                    </div>
                    <div>
                        <label>LinkedIn Profile:</label>
                        <input type="url" name="linkedin" value="<?php echo htmlspecialchars($alumni['linkedin']); ?>">
                    </div>
                </div>
                <div class="form-group">
                    <div>
                        <label>GitHub Profile:</label>
                        <input type="url" name="github" value="<?php echo htmlspecialchars($alumni['github']); ?>">
                    </div>
                </div>
            </div>

            <!-- Educational Details Section -->
            <div class="section">
                <h3>Educational Details</h3>
                <div class="form-group">
                    <div>
                        <label>PRN Number:</label>
                        <input type="text" name="prn_no" value="<?php echo htmlspecialchars($alumni['prn_no']); ?>" required pattern="[0-9]{2}[A-Z]{2}[A-Z]{2}[0-9]{5}" placeholder="12UGCS34567" title="PRN should be in the format 12UGCS34567">
                    </div>
                    <div>
                        <label>Undergraduate Degree:</label>
                        <input type="text" name="ug_degree" value="<?php echo htmlspecialchars($alumni['ug_degree']); ?>" required>
                    </div>
                    <div>
                        <label>Undergraduate Institute:</label>
                        <input type="text" name="ug_institute" value="<?php echo htmlspecialchars($alumni['ug_institute']); ?>" required>
                    </div>
                </div>
                <div class="form-group">
                    <div>
                        <label>Undergraduate Graduation Year:</label>
                        <input type="text" name="ug_graduation_year" value="<?php echo htmlspecialchars($alumni['ug_graduation_year']); ?>" required>
                    </div>
                    <div>
                        <label>Postgraduate Degree:</label>
                        <input type="text" name="pg_degree" value="<?php echo htmlspecialchars($alumni['pg_degree']); ?>">
                    </div>
                    <div>
                        <label>Postgraduate Institute:</label>
                        <input type="text" name="pg_institute" value="<?php echo htmlspecialchars($alumni['pg_institute']); ?>">
                    </div>
                </div>
                <div class="form-group">
                    <div>
                        <label>Postgraduate Graduation Year:</label>
                        <input type="text" name="pg_graduation_year" value="<?php echo htmlspecialchars($alumni['pg_graduation_year']); ?>">
                    </div>
                    <div>
                        <label>Department:</label>
                        <select name="department" required>
                            <option value="CSE" <?php echo $alumni['department'] === 'CSE' ? 'selected' : ''; ?>>Computer Science & Engineering</option>
                            <option value="ENTC" <?php echo $alumni['department'] === 'ENTC' ? 'selected' : ''; ?>>Electronics & Telecommunication</option>
                            <option value="Mechanical" <?php echo $alumni['department'] === 'Mechanical' ? 'selected' : ''; ?>>Mechanical Engineering</option>
                            <option value="Civil" <?php echo $alumni['department'] === 'Civil' ? 'selected' : ''; ?>>Civil Engineering</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Company Details Section -->
            <div class="section">
                <h3>Company Details</h3>
                <div class="form-group">
                    <div>
                        <label>Company Name:</label>
                        <input type="text" name="company" value="<?php echo htmlspecialchars($alumni['company']); ?>">
                    </div>
                    <div>
                        <label>Years of Experience:</label>
                        <input type="number" name="experience" min="0" value="<?php echo htmlspecialchars($alumni['experience']); ?>">
                    </div>
                    <div>
                        <label>Position:</label>
                        <input type="text" name="position" value="<?php echo htmlspecialchars($alumni['position']); ?>">
                    </div>
                </div>
                <div class="form-group full-width">
                    <div>
                        <label>Skills:</label>
                        <textarea name="skills" placeholder="E.g., Python, Java, Management"><?php echo htmlspecialchars($alumni['skills']); ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Other Information Section -->
            <div class="section">
                <h3>Other Information</h3>
                <div class="form-group">
                    <div>
                        <label>Emergency Contact:</label>
                        <input type="tel" name="emergency_contact" value="<?php echo htmlspecialchars($alumni['emergency_contact']); ?>">
                    </div>
                    <div>
                        <label>Job Profile:</label>
                        <select name="job_profile" required>
                            <option value="Entrepreneur" <?php echo $alumni['job_profile'] === 'Entrepreneur' ? 'selected' : ''; ?>>Entrepreneur</option>
                            <option value="Government" <?php echo $alumni['job_profile'] === 'Government' ? 'selected' : ''; ?>>Government</option>
                            <option value="IT" <?php echo $alumni['job_profile'] === 'IT' ? 'selected' : ''; ?>>IT</option>
                            <option value="Education" <?php echo $alumni['job_profile'] === 'Education' ? 'selected' : ''; ?>>Education</option>
                            <option value="Healthcare" <?php echo $alumni['job_profile'] === 'Healthcare' ? 'selected' : ''; ?>>Healthcare</option>
                        </select>
                    </div>
                </div>
            </div>

            <button type="submit" class="Update">Update Profile</button>
        </form>
    </div>

    <footer>
        <p>© <?php echo date("Y"); ?> Alumni Association Platform. All rights reserved.</p>
    </footer>

    <script>
        function previewImage(event) {
            const preview = document.getElementById('profile_preview');
            preview.style.display = 'block';
            preview.src = URL.createObjectURL(event.target.files[0]);
        }
    </script>
</body>
</html>