<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

function send_email($to_emails, $subject, $message) {
    $mail = new PHPMailer(true);
    try {
        // SMTP configuration
        $mail->isSMTP();
        $mail->SMTPAuth = true;
        $mail->Host = 'smtp.gmail.com';
        $mail->Username = 'munirshikalgar123@gmail.com'; // Your email
        $mail->Password = 'zgho ozar curd hcyp'; // Your App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Sender
        $mail->setFrom('munirshikalgar123@gmail.com', 'Alumni Portal');

        // Add multiple recipients
        if (is_array($to_emails)) {
            foreach ($to_emails as $email) {
                $mail->addAddress($email, 'Recipient');
            }
        } else {
            $mail->addAddress($to_emails, 'Recipient');
        }

        // Email content
        $mail->Subject = $subject;
        $mail->Body = $message;
        $mail->isHTML(false); // Plain text email

        if ($mail->send()) {
            return true;
        } else {
            error_log("Email failed: " . $mail->ErrorInfo);
            return false;
        }
    } catch (Exception $e) {
        error_log("Email failed: " . $e->getMessage());
        return false;
    }
}
?>