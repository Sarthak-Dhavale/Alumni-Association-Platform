<?php
require 'db.php';
require 'send_email.php'; // Email sending function

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
        $target_dir = "Uploads/";
        $file_extension = strtolower(pathinfo($_FILES["profile_picture"]["name"], PATHINFO_EXTENSION));
        $new_filename = uniqid() . '.' . $file_extension;
        $target_file = $target_dir . $new_filename;
        
        // Validate image
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        $max_size = 50000000; // 50MB

        if (!in_array($file_extension, $allowed_types)) {
            $errors[] = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
        }

        if ($_FILES["profile_picture"]["size"] > $max_size) {
            $errors[] = "Sorry, your file must be less than 50MB.";
        }

        if (empty($errors) && !move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
            $errors[] = "Sorry, there was an error uploading your file.";
            $target_file = null;
        }
    }

    // Proceed with registration if no errors
    if (empty($errors)) {
        try {
            // Insert into users table with "pending" status
            $stmt = $pdo->prepare("INSERT INTO users (
                name, dob, gender, address,
                email, phone, linkedin, github,
                ug_degree, ug_institute, ug_graduation_year,
                pg_degree, pg_institute, pg_graduation_year,
                department, prn_no, company, experience, position, skills,
                emergency_contact, job_profile, profile_picture, status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')");

            $stmt->execute([
                $full_name, $dob, $gender, $address,
                $email, $phone, $linkedin, $github,
                $ug_degree, $ug_institute, $ug_graduation_year,
                $pg_degree, $pg_institute, $pg_graduation_year,
                $department, $prn_no, $company, $experience, $position, $skills,
                $emergency_contact, $job_profile, $target_file
            ]);

            // Fetch admin and coordinator emails
            $recipients = [];
            
            // Fetch admin email
            $stmt = $pdo->prepare("SELECT email FROM users WHERE role = 'admin' AND status = 'verified' LIMIT 1");
            $stmt->execute();
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);
            $admin_email = $admin ? $admin['email'] : 'munirshikalgar123@gmail.com'; // Fallback
            $recipients[] = $admin_email;

            // Fetch coordinator email for the selected department
            $stmt = $pdo->prepare("SELECT email FROM users WHERE role = 'coordinator' AND department = ? AND status = 'verified' LIMIT 1");
            $stmt->execute([$department]);
            $coordinator = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($coordinator) {
                $recipients[] = $coordinator['email'];
            }

            // Construct the email message
            $subject = "Alumni Registration Verification Request";
            $message = "A new alumni has registered and requires verification. Their details are as follows:\n\n";
            $message .= "Full Name: $full_name\n";
            $message .= "Date of Birth: $dob\n";
            $message .= "Gender: $gender\n";
            $message .= "Address: $address\n\n";
            $message .= "Contact Details:\n";
            $message .= "Email: $email\n";
            $message .= "Phone: $phone\n";
            $message .= "LinkedIn: $linkedin\n";
            $message .= "GitHub: $github\n\n";
            $message .= "Educational Details:\n";
            $message .= "PRN Number: $prn_no\n";
            $message .= "Undergraduate Degree: $ug_degree\n";
            $message .= "Undergraduate Institute: $ug_institute\n";
            $message .= "UG Graduation Year: $ug_graduation_year\n";
            $message .= "Postgraduate Degree: $pg_degree\n";
            $message .= "Postgraduate Institute: $pg_institute\n";
            $message .= "PG Graduation Year: $pg_graduation_year\n";
            $message .= "Department: $department\n\n";
            $message .= "Company Details:\n";
            $message .= "Company Name: $company\n";
            $message .= "Experience: $experience years\n";
            $message .= "Position: $position\n";
            $message .= "Skills: $skills\n\n";
            $message .= "Other Information:\n";
            $message .= "Emergency Contact: $emergency_contact\n";
            $message .= "Job Profile: $job_profile\n\n";

            if ($target_file) {
                $message .= "Profile Picture: " . $_SERVER['HTTP_HOST'] . "/Uploads/" . basename($target_file) . "\n\n";
            }

            $message .= "Please log in to the Alumni Portal to approve or reject this registration.\n";
            $message .= "Best Regards,\n";
            $message .= "Alumni Registration Team";

            // Send email to admin and coordinator
            if (send_email($recipients, $subject, $message)) {
                $success_message = "Registration successful! Please wait for approval from admin and department coordinator.";
            } else {
                $errors[] = "Email notification failed. Please contact support.";
            }

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
            max-width: 1200px;
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
            margin-bottom: 40px;
            padding: 25px;
            background: #f8f9fa;
            border-radius: 8px;
        }

        h3 {
            color: #3498db;
            margin-bottom: 25px;
            padding-bottom: 10px;
            border-bottom: 2px solid #3498db;
        }

        .form-group {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 30px;
            margin-bottom: 25px;
        }

        .form-group.full-width {
            grid-template-columns: 1fr;
        }

        .form-group > div {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #2c3e50;
        }

        .required::after {
            content: '*';
            color: red;
            margin-left: 2px;
        }

        input[type="text"],
        input[type="email"],
        input[type="tel"],
        input[type="url"],
        input[type="date"],
        input[type="number"] {
            height: 20px;
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
            padding: 9px;
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
            padding: 15px 30px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
            transition: background-color 0.3s;
            margin-top: 20px;
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
            text-align: center;
            font-size: 18px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
        }

        @keyframes tick-anim {
            0% { transform: scale(0) rotate(45deg); }
            100% { transform: scale(1) rotate(45deg); }
        }

        #profile_preview {
            max-width: 200px;
            max-height: 200px;
            margin-top: 10px;
            display: none;
        }

        @media (max-width: 768px) {
            .form-group {
                grid-template-columns: 1fr;
                gap: 20px;
            }

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
            <script>
                // Redirect to login page after 3 seconds
                setTimeout(() => {
                    window.location.href = 'login.php';
                }, 3000);
            </script>
        <?php else: ?>
            <form action="" method="post" enctype="multipart/form-data">
                <!-- Personal Details Section -->
                <div class="section">
                    <h3>Personal Details</h3>
                    <div class="form-group">
                        <div>
                            <label class="required">Full Name:</label>
                            <input type="text" name="full_name" required>
                        </div>
                        <div>
                            <label class="required">Date of Birth:</label>
                            <input type="date" name="dob" required>
                        </div>
                        <div>
                            <label class="required">Gender:</label>
                            <select name="gender" required>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group full-width">
                        <div>
                            <label class="required">Address:</label>
                            <textarea name="address" required></textarea>
                        </div>
                    </div>
                    <div class="form-group">
                        <div>
                            <label>Profile Picture:</label>
                            <input type="file" name="profile_picture" accept="image/*" onchange="previewImage(event)">
                            <img id="profile_preview" alt="Profile Picture Preview">
                        </div>
                    </div>
                </div>

                <!-- Contact Details Section -->
                <div class="section">
                    <h3>Contact Details</h3>
                    <div class="form-group">
                        <div>
                            <label class="required">Email:</label>
                            <input type="email" name="email" required>
                        </div>
                        <div>
                            <label class="required">Phone Number:</label>
                            <input type="tel" name="phone" required>
                        </div>
                        <div>
                            <label>LinkedIn Profile:</label>
                            <input type="url" name="linkedin">
                        </div>
                    </div>
                    <div class="form-group">
                        <div>
                            <label>GitHub Profile:</label>
                            <input type="url" name="github">
                        </div>
                    </div>
                </div>

                <!-- Educational Details Section -->
                <div class="section">
                    <h3>Educational Details</h3>
                    <div class="form-group">
                        <div>
                            <label class="required">PRN Number:</label>
                            <input type="text" name="prn_no" required pattern="[0-9]{2}[A-Z]{2}[A-Z]{2}[0-9]{5}" placeholder="12UGCS34567" title="PRN should be in the format 12UGCS34567">
                        </div>
                        <div>
                            <label class="required">Undergraduate Degree:</label>
                            <input type="text" name="ug_degree" required>
                        </div>
                        <div>
                            <label class="required">Undergraduate Institute:</label>
                            <input type="text" name="ug_institute" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <div>
                            <label class="required">Undergraduate Graduation Year:</label>
                            <input type="text" name="ug_graduation_year" required>
                        </div>
                        <div>
                            <label>Postgraduate Degree:</label>
                            <input type="text" name="pg_degree">
                        </div>
                        <div>
                            <label>Postgraduate Institute:</label>
                            <input type="text" name="pg_institute">
                        </div>
                    </div>
                    <div class="form-group">
                        <div>
                            <label>Postgraduate Graduation Year:</label>
                            <input type="text" name="pg_graduation_year">
                        </div>
                        <div>
                            <label class="required">Department:</label>
                            <select name="department" required>
                                <option value="CSE">Computer Science & Engineering</option>
                                <option value="ENTC">Electronics & Telecommunication</option>
                                <option value="Mechanical">Mechanical Engineering</option>
                                <option value="Civil">Civil Engineering</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Company Details Section -->
                <div class="section">
                    <h3>Company Details</h3>
                    <div class="form-group">
                        <div>
                            <label class="required">Company Name:</label>
                            <input type="text" name="company" required>
                        </div>
                        <div>
                            <label class="required">Years of Experience:</label>
                            <input type="number" name="experience" min="0" required>
                        </div>
                        <div>
                            <label class="required">Position:</label>
                            <input type="text" name="position" required>
                        </div>
                    </div>
                    <div class="form-group full-width">
                        <div>
                            <label class="required">Skills:</label>
                            <textarea name="skills" placeholder="E.g., Python, Java, Management" required></textarea>
                        </div>
                    </div>
                </div>

                <!-- Other Information Section -->
                <div class="section">
                    <h3>Other Information</h3>
                    <div class="form-group">
                        <div>
                            <label>Emergency Contact:</label>
                            <input type="tel" name="emergency_contact">
                        </div>
                        <div>
                            <label class="required">Job Profile:</label>
                            <select name="job_profile" required>
                                <option value="Entrepreneur">Entrepreneur</option>
                                <option value="Government">Government</option>
                                <option value="IT">IT</option>
                                <option value="Education">Education</option>
                                <option value="Healthcare">Healthcare</option>
                            </select>
                        </div>
                    </div>
                </div>

                <button type="submit" name="submitform">Register</button>
            </form>
        <?php endif; ?>
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
<?php include 'includes/footer.php'; ?>