<?php
session_start();
include __DIR__ . '/../includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error = "";
$success = "";

// Submit Feedback
if (isset($_POST['submit'])) {

    $message = trim($_POST['message']);

    if (empty($message)) {
        $error = "Feedback cannot be empty.";
    } else {

        $stmt = $pdo->prepare("INSERT INTO feedback (Message, U_Id) VALUES (?, ?)");
        
        if ($stmt->execute([$message, $user_id])) {
            $success = "Feedback submitted successfully!";
        } else {
            $error = "Something went wrong.";
        }
    }
}

// Fetch user's feedback history
$stmt = $pdo->prepare("SELECT * FROM feedback WHERE U_Id = ? ORDER BY F_Id DESC");
$stmt->execute([$user_id]);
$feedbacks = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>EcoGuard | Give Feedback</title>
    <link rel="stylesheet" href="../css/give_feedback2.css">
</head>
<body>
<?php 
$path_to_root = "../";
include __DIR__ . '/../header.php'; 
?>
<header>
    <h1>Give Feedback</h1>
</header>

<div class="dashboard">


    <!-- MAIN CONTENT -->
    <div class="main-content">

        <?php if ($error): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <?php if ($success): ?>
            <p class="success"><?= htmlspecialchars($success) ?></p>
        <?php endif; ?>

        <form method="POST" class="complaint-form">
            <label>Your Feedback</label>
            <textarea name="message" rows="6" required></textarea>
            <button type="submit" name="submit">Submit Feedback</button>
        </form>

        <hr>

        <h2>Your Previous Feedback</h2>

        <?php if (count($feedbacks) > 0): ?>
            <?php foreach ($feedbacks as $row): ?>
                <div class="event-card">
                    <p><strong>Your Message:</strong><br>
                        <?= htmlspecialchars($row['Message']) ?>
                    </p>

                    <?php if (!empty($row['Admin_Reply'])): ?>
    <p style="margin-top:10px; color:green;">
        <strong>Admin Reply:</strong><br>
        <?= htmlspecialchars($row['Admin_Reply']) ?>
    </p>
<?php else: ?>
    <p style="color:gray;">Awaiting admin response...</p>
<?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No feedback submitted yet.</p>
        <?php endif; ?>

    </div>

</div>
<?php include __DIR__ . '/../footer.php'; ?>
</body>
</html>