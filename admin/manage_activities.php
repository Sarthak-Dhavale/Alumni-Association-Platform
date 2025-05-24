<?php
// /admin/manage_events.php
require '../db.php';
session_start();

if ($_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

// Fetch all events
$events = $pdo->query("SELECT * FROM events")->fetchAll();
?>

<h2>Manage Events</h2>
<table border="1">
    <thead>
        <tr>
            <th>Title</th>
            <th>Description</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($events as $event): ?>
            <tr>
                <td><?php echo htmlspecialchars($event['title']); ?></td>
                <td><?php echo htmlspecialchars($event['description']); ?></td>
                <td><?php echo htmlspecialchars($event['status']); ?></td>
                <td>
                    <a href="approve_event.php?id=<?php echo $event['id']; ?>">Approve</a> |
                    <a href="delete_event.php?id=<?php echo $event['id']; ?>">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
