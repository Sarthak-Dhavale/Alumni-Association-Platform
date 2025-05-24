<?php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents("php://input"), true);

    $user_id = $_SESSION['user_id'] ?? null;
    $people_count = $input['people_count'] ?? 0;
    $stay_days = $input['stay_days'] ?? 0;
    $activity_id = $input['activity_id'] ?? null;

    if (!$user_id || !$activity_id || $people_count < 1) {
        http_response_code(400);
        echo "Invalid input.";
        exit;
    }

    // Fetch user name and address from users table
    $stmt = $pdo->prepare("SELECT name, address FROM users WHERE prn_no = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        http_response_code(400);
        echo "User not found.";
        exit;
    }

    // Insert attendance
    $stmt = $pdo->prepare("INSERT INTO alumni_attendance (activity_id, user_id, alumni_name, address, people_count, stay_days) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$activity_id, $user_id, $user['name'], $user['address'], $people_count, $stay_days]);

    echo "Your attendance has been recorded!";
}
?>
