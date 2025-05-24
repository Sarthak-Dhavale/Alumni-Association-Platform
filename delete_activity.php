<?php
// delete_activity.php
session_start();
require 'db.php'; // Ensure database connection

// Check if the user is an admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("Unauthorized access");
}

// Check if activity ID is provided via POST
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'])) {
    $activity_id = intval($_POST['id']); // Prevent SQL injection

    try {
        // Begin transaction
        $pdo->beginTransaction();

        // Delete attendance records related to the activity
        $stmt = $pdo->prepare("DELETE FROM alumni_attendance WHERE activity_id = ?");
        $stmt->execute([$activity_id]);

        // Delete the activity
        $stmt = $pdo->prepare("DELETE FROM activities WHERE id = ?");
        $stmt->execute([$activity_id]);

        // Commit transaction
        $pdo->commit();

        // Redirect back to activities page with success message
        header("Location: activities.php?success=deleted");
        exit();
    } catch (PDOException $e) {
        // Rollback transaction on error
        $pdo->rollBack();
        header("Location: activities.php?error=" . urlencode("Error deleting activity: " . $e->getMessage()));
        exit();
    }
} else {
    header("Location: activities.php?error=Invalid request");
    exit();
}
?>