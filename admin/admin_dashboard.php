<?php
// admin_dashboard.php
require 'db.php';
session_start();

if ($_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit();
}

// Fetch pending events, job opportunities, and success stories
$events = $pdo->query("SELECT * FROM events WHERE status = 'pending'")->fetchAll();
$jobs = $pdo->query("SELECT * FROM job_opportunities WHERE approved = FALSE")->fetchAll();
$stories = $pdo->query("SELECT * FROM success_stories WHERE status = 'pending'")->fetchAll();
?>

<h2>Admin Dashboard</h2>

<h3>Pending Events</h3>
<ul>
    <?php foreach ($events as $event): ?>
        <li><?php echo htmlspecialchars($event['title']); ?> <a href="approve_event.php?id=<?php echo $event['id']; ?>">Approve</a></li>
    <?php endforeach; ?>
</ul>

<h3>Job Submissions</h3>
<ul>
    <?php foreach ($jobs as $job): ?>
        <li><?php echo htmlspecialchars($job['title']); ?> <a href="approve_job.php?id=<?php echo $job['id']; ?>">Approve</a></li>
    <?php endforeach; ?>
</ul>

<h3>Pending Success Stories</h3>
<ul>
    <?php foreach ($stories as $story): ?>
        <li><?php echo htmlspecialchars($story['title']); ?> <a href="approve_story.php?id=<?php echo $story['id']; ?>">Approve</a></li>
    <?php endforeach; ?>
</ul>
