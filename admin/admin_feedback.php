<?php
session_start();
include __DIR__ . '/../includes/db.php';

// Only Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 2) {
    header("Location: ../login.php");
    exit();
}

$admin_id = $_SESSION['user_id'];

// Fetch admin info
$stmtAdmin = $pdo->prepare("SELECT * FROM users WHERE U_Id = ?");
$stmtAdmin->execute([$admin_id]);
$admin = $stmtAdmin->fetch(PDO::FETCH_ASSOC);

// Fetch all feedback with citizen info
$stmt = $pdo->query("
    SELECT f.*, u.F_name, u.L_name, u.Email 
    FROM feedback f
    JOIN users u ON f.U_Id = u.U_Id
    ORDER BY f.F_Id DESC
");
$feedbacks = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle admin replies
if (isset($_POST['submit_reply'])) {
    $reply = trim($_POST['reply']);
    $feedback_id = $_POST['feedback_id'];

    if (!empty($reply)) {
        $stmtReply = $pdo->prepare("UPDATE feedback SET Admin_Reply = ? WHERE F_Id = ?");
        $stmtReply->execute([$reply, $feedback_id]);
        header("Location: admin_feedback.php?success=1");
        exit();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>EcoGuard | Admin Feedback</title>
    <link rel="stylesheet" href="../css/admin_feedback.css">
    <style>
        .feedback-card {
            border: 1px solid #ccc;
            border-radius: 6px;
            padding: 12px;
            margin-bottom: 15px;
            background-color: #f9f9f9;
        }
        .feedback-card h4 { margin: 0 0 5px; }
        .feedback-card p { margin: 3px 0; }
        .feedback-card form textarea {
            width: 100%;
            padding: 6px;
            margin-top: 5px;
            border-radius: 4px;
            border: 1px solid #ccc;
            resize: vertical;
        }
        .feedback-card form button {
            margin-top: 5px;
            padding: 6px 10px;
            border: none;
            background-color: #0b79d0;
            color: white;
            border-radius: 4px;
            cursor: pointer;
        }
        .feedback-card form button:hover { background-color: #095a9d; }
        .success-msg { color: green; margin-bottom: 10px; }
    </style>
</head>
<body>
<header>
    <h1>EcoGuard Admin Feedback Panel</h1>
</header>

<div class="dashboard">
    <div class="sidebar">
        <h3><?= htmlspecialchars($admin['F_name']) ?></h3>
        <p><?= htmlspecialchars($admin['Email']) ?></p>
        <p><?= htmlspecialchars($admin['Tele']) ?></p>
        <button onclick="location.href='admin_dash.php'">⬅ Back</button>
    </div>

    <div class="main-content">
        <h2>Citizen Feedback</h2>

        <?php if (isset($_GET['success'])): ?>
            <div class="success-msg"> Reply submitted successfully!</div>
        <?php endif; ?>

        <?php if (count($feedbacks) > 0): ?>
            <?php foreach ($feedbacks as $fb): ?>
                <div class="feedback-card">
                    <h4><?= htmlspecialchars($fb['F_name'] . ' ' . $fb['L_name']) ?> (<?= htmlspecialchars($fb['Email']) ?>)</h4>
                    <p><strong>Feedback:</strong> <?= nl2br(htmlspecialchars($fb['Message'])) ?></p>
                    <?php if (!empty($fb['Admin_Reply'])): ?>
                        <p><strong>Admin Reply:</strong> <?= nl2br(htmlspecialchars($fb['Admin_Reply'])) ?></p>
                    <?php else: ?>
                        <form method="POST">
                            <input type="hidden" name="feedback_id" value="<?= $fb['F_Id'] ?>">
                            <textarea name="reply" rows="3" placeholder="Write a reply..." required></textarea>
                            <button type="submit" name="submit_reply">Reply</button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No feedback submitted yet.</p>
        <?php endif; ?>
    </div>
</div>
</body>
</html>