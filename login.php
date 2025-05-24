<?php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT prn_no, password, role, department, status FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                if ($user['status'] === 'verified') {
                    $_SESSION['user_id'] = $user['prn_no'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['department'] = $user['department'];
                    header('Location: index.php');
                    exit();
                } else {
                    $error = "Your account is not verified. Please contact the administrator.";
                }
            } else {
                $error = "Invalid email or password.";
            }
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Alumni Association Platform</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Segoe+UI:wght@300;400;600&display=swap">
    <style>
        /* General Styles */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-image: url('https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTKf93PL3rCzesRa0-yhBLmQFWsOKY_xJfRFg&s');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            overflow: hidden;
        }

        .login-container {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0); /* Original opacity */
            backdrop-filter: blur(5px);
        }

        .login-box {
            background: rgba(255, 255, 255, 0.8);
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 420px;
            text-align: center;
            position: relative;
            animation: slideUp 0.5s ease-out;
        }

        .login-box h2 {
            margin-bottom: 10px;
            font-size: 28px;
            color: #1A1A1A; /* Dark Black */
            font-weight: 600;
            position: relative;
        }

        .login-box h2::after {
            content: '';
            position: absolute;
            bottom: -8px;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 3px;
            background: linear-gradient(90deg, transparent, #1A1A1A, transparent);
        }

        .login-box p {
            margin-bottom: 25px;
            color: #555;
            font-size: 16px;
        }

        .input-group {
            margin-bottom: 20px;
            text-align: left;
            position: relative;
        }

        .input-group input {
            width: 100%;
            padding: 12px 40px 12px 12px;
            border: 2px solid #ccc;
            border-radius: 6px;
            font-size: 16px;
            background-color: #f9f9f9;
            outline: none;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
            box-sizing: border-box;
        }

        .input-group input:focus {
            border-color: #1A1A1A;
            box-shadow: 0 0 8px rgba(26, 26, 26, 0.3);
        }

        .input-group input::placeholder {
            color: #888;
        }

        .input-group i {
            position: absolute;
            top: 50%;
            right: 12px;
            transform: translateY(-50%);
            color: #1A1A1A;
            font-size: 18px;
        }

        .btn-login {
            width: 100%;
            padding: 12px;
            background-color: #1A1A1A;
            color: #fff;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease;
            position: relative;
            overflow: hidden;
        }

        .btn-login:hover {
            background-color: #333333; /* Lighter Black/Grey for hover */
            transform: translateY(-2px);
        }

        .btn-login::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            transition: width 0.6s ease, height 0.6s ease;
        }

        .btn-login:hover::before {
            width: 300px;
            height: 300px;
        }

        .alert-error {
            background-color: rgba(255, 235, 238, 0.9);
            color: #c62828;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
            box-shadow: 0 2px 5px rgba(198, 40, 40, 0.1);
        }

        .register-link {
            margin-top: 20px;
            color: #555;
            font-size: 14px;
        }

        .register-link a {
            color: #1A1A1A;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .register-link a:hover {
            color: #333333;
            text-decoration: underline;
        }

        /* Animations */
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive Design */
        @media (max-width: 480px) {
            .login-box {
                padding: 30px;
                max-width: 90%;
            }

            .login-box h2 {
                font-size: 24px;
            }

            .input-group input, .btn-login {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <h2>TKIET Alumni Platform</h2>
            <p>Please login to your account</p>

            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <form action="login.php" method="post">
                <div class="input-group">
                    <input type="email" name="email" placeholder="Email" required>
                    <i class="fas fa-envelope"></i>
                </div>
                <div class="input-group">
                    <input type="password" name="password" placeholder="Password" required>
                    <i class="fas fa-lock"></i>
                </div>
                <button type="submit" class="btn-login">Login</button>
            </form>

            <p class="register-link">Don't have an account? <a href="register.php">Register here</a></p>
        </div>
    </div>
</body>
</html>
<?php include 'includes/footer.php'; ?>