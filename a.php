<?php
session_start();
require 'db.php';

// Check if the user is an admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("Unauthorized access");
}

// Check if activity ID is provided via POST
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'])) {
    $activity_id = intval($_POST['id']);

    try {
        // Delete attendance records related to the activity
        $stmt = $pdo->prepare("DELETE FROM alumni_attendance WHERE id = ?");
        $stmt->execute([$activity_id]);

        // Delete the activity
        $stmt = $pdo->prepare("DELETE FROM activities WHERE id = ?");
        $stmt->execute([$activity_id]);

        // Redirect back to activities page with success message
        header("Location: activities.php?success=deleted");
        exit();
    } catch (PDOException $e) {
        die("Error deleting activity: " . $e->getMessage());
    }
} else {
    die("Invalid request");
}
?>