<?php
session_start();
include __DIR__ . '/../includes/db.php';

// Only Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 2) {
    header("Location: ../login.php");
    exit();
}

if (isset($_GET['id'])) {
    $event_id = $_GET['id'];

    // 1. Delete participants FIRST (because of foreign key)
    $stmt = $pdo->prepare("DELETE FROM user_participate_volunteer_event WHERE Event_Id = ?");
    $stmt->execute([$event_id]);

    // 2. Then delete the event
    $stmt = $pdo->prepare("DELETE FROM volunteer_event WHERE Event_Id = ?");
    $stmt->execute([$event_id]);
}

// Redirect back
header("Location: view_volunteer_events.php");
exit();