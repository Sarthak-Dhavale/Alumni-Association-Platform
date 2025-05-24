<?php
require 'db.php';
//register.php
// Function to sanitize input
function sanitize_input($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

$errors = [];
$success_message = '';

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
    $target_file = null;
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $profile_picture = $_FILES['profile_picture'];
        $target_dir = "uploads/";
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

        if (empty($errors) && move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
            // File uploaded successfully
        } else {
            $errors[] = "Sorry, there was an error uploading your file.";
            $target_file = null;
        }
    }

    // Proceed with registration if no errors
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO users (
                name, dob, gender, address,
                email, phone, linkedin, github,
                ug_degree, ug_institute, ug_graduation_year,
                pg_degree, pg_institute, pg_graduation_year,
                department, prn_no, company, experience, position, skills,
                emergency_contact, job_profile, profile_picture
            ) VALUES (
                ?, ?, ?, ?, 
                ?, ?, ?, ?,
                ?, ?, ?,
                ?, ?, ?,
                ?, ?, ?, ?, ?, ?,
                ?, ?, ?
            )");
            
            $stmt->execute([
                $full_name, $dob, $gender, $address,
                $email, $phone, $linkedin, $github,
                $ug_degree, $ug_institute, $ug_graduation_year,
                $pg_degree, $pg_institute, $pg_graduation_year,
                $department, $prn_no, $company, $experience, $position, $skills,
                $emergency_contact, $job_profile, $target_file
            ]);
            

            $success_message = "Registration successful! Redirecting...";
            header("Refresh: 2; URL=directory.php");
        } catch (PDOException $e) {
            $errors[] = "Database Error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alumni Registration Form</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
            color: #333;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }

        h2 {
            color: #2c3e50;
            text-align: center;
            margin-bottom: 30px;
        }

        .section {
            margin-bottom: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
        }

        h3 {
            color: #3498db;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #3498db;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #2c3e50;
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
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            transition: border-color 0.3s;
        }

        input:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: #3498db;
        }

        textarea {
            height: 100px;
            resize: vertical;
        }

        button {
            background-color: #3498db;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #2980b9;
        }

        .error {
            color: #e74c3c;
            margin-bottom: 20px;
        }

        .success {
            color: #27ae60;
            margin-bottom: 20px;
        }

        #profile_preview {
            max-width: 200px;
            max-height: 200px;
            margin-top: 10px;
            display: none;
        }

        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Alumni Registration Form</h2>
        
        <?php if (!empty($errors)): ?>
            <div class="error">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo $error; ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($success_message): ?>
            <div class="success">
                <p><?php echo $success_message; ?></p>
            </div>
        <?php endif; ?>

        <form action="register.php" method="post" enctype="multipart/form-data">
            <!-- Personal Details Section -->
            <div class="section">
                <h3>Personal Details</h3>
                <div class="form-group">
                    <label>Full Name:</label>
                    <input type="text" name="full_name" required>
                </div>

                <div class="form-group">
                    <label>Date of Birth:</label>
                    <input type="date" name="dob" required>
                </div>
                <div class="form-group">
                    <label>Gender:</label>
                    <select name="gender" required>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Address:</label>
                    <textarea name="address" required></textarea>
                </div>
                <div class="form-group">
                    <label>Profile Picture:</label>
                    <input type="file" name="profile_picture" accept="image/*" onchange="previewImage(event)">
                    <img id="profile_preview" alt="Profile Picture Preview">
                </div>
            </div>

            <!-- Contact Details Section -->
            <div class="section">
                <h3>Contact Details</h3>
                <div class="form-group">
                    <label>Email:</label>
                    <input type="email" name="email" required>
                </div>
                <div class="form-group">
                    <label>Phone Number:</label>
                    <input type="tel" name="phone" required>
                </div>
                <div class="form-group">
                    <label>LinkedIn Profile:</label>
                    <input type="url" name="linkedin">
                </div>
                <div class="form-group">
                    <label>GitHub Profile:</label>
                    <input type="url" name="github">
                </div>
            </div>

            <!-- Educational Details Section -->
            <div class="section">
                <h3>Educational Details</h3>
                <div class="form-group">
                    <label>PRN Number:</label>
                    <input type="text" name="prn_no" required pattern="[0-9]{8}" title="Please enter a valid 8-digit PRN number">
                </div>
                <div class="form-group">
                    <label>Undergraduate Degree:</label>
                    <input type="text" name="ug_degree" required>
                </div>
                <div class="form-group">
                    <label>Undergraduate Institute:</label>
                    <input type="text" name="ug_institute" required>
                </div>
                <div class="form-group">
                    <label>Undergraduate Graduation Year:</label>
                    <input type="text" name="ug_graduation_year" required>
                </div>
                <div class="form-group">
                    <label>Postgraduate Degree:</label>
                    <input type="text" name="pg_degree">
                </div>
                <div class="form-group">
                    <label>Postgraduate Institute:</label>
                    <input type="text" name="pg_institute">
                </div>
                <div class="form-group">
                    <label>Postgraduate Graduation Year:</label>
                    <input type="text" name="pg_graduation_year">
                </div>
                <div class="form-group">
                    <label>Department:</label>
                    <select name="department" required>
                        <option value="CSE">Computer Science & Engineering</option>
                        <option value="ENTC">Electronics & Telecommunication</option>
                        <option value="Mechanical">Mechanical Engineering</option>
                        <option value="Civil">Civil Engineering</option>
                    </select>
                </div>
            </div>

            <!-- Company Details Section -->
            <div class="section">
                <h3>Company Details</h3>
                <div class="form-group">
                    <label>Company Name:</label>
                    <input type="text" name="company">
                </div>
                <div class="form-group">
                    <label>Years of Experience:</label>
                    <input type="number" name="experience" min="0">
                </div>
                <div class="form-group">
                    <label>Position:</label>
                    <input type="text" name="position">
                </div>
                <div class="form-group">
                    <label>Skills:</label>
                    <textarea name="skills" placeholder="E.g., Python, Java, Management"></textarea>
                </div>
            </div>

            <!-- Other Information Section -->
            <div class="section">
                <h3>Other Information</h3>
                <div class="form-group">
                    <label>Emergency Contact:</label>
                    <input type="tel" name="emergency_contact" required>
                </div>
                <div class="form-group">
                    <label>Job Profile:</label>
                    <select name="job_profile" required>
                        <option value="Entrepreneur">Entrepreneur</option>
                        <option value="Government">Government</option>
                        <option value="IT">IT</option>
                        <option value="Education">Education</option>
                        <option value="Healthcare">Healthcare</option>
                    </select>
                </div>
            </div>

            <button type="submit">Register</button>
        </form>
    </div>

    <script>
        function previewImage(event) {
            const preview = document.getElementById('profile_preview');
            preview.style.display = 'block';
            preview.src = URL.createObjectURL(event.target.files[0]);
        }
    </script>
</body>
</html>