<?php
session_start();
include __DIR__ . '/../includes/db.php';

// Redirect if not logged in or not Citizen
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch logged-in user info
$stmtUser = $pdo->prepare("SELECT * FROM users WHERE U_Id = ?");
$stmtUser->execute([$user_id]);
$user = $stmtUser->fetch(PDO::FETCH_ASSOC);

// Fetch citizen complaints
$stmt = $pdo->prepare("SELECT * FROM complaint WHERE U_Id = ? ORDER BY C_Id DESC");
$stmt->execute([$user_id]);
$complaints = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>EcoGuard | Complaint History</title>
    <link rel="stylesheet" href="../css/history.css"> 
</head>
<body>
<?php 
$path_to_root = "../";
include __DIR__ . '/../header.php'; 
?>

<header>
    <h1>EcoGuard Citizen Panel</h1>
</header>

<div class="dashboard">

    <!-- MAIN CONTENT -->
    <div class="main-content">
        <h2>Your Complaint History</h2>

        <?php if (count($complaints) > 0): ?>
            <div class="complaint-cards">
                <?php foreach ($complaints as $complaint): ?>
                    <div class="complaint-card">
                        <h3>Complaint #<?= htmlspecialchars($complaint['C_Id']) ?></h3>
                        <p><strong>Details:</strong> <?= htmlspecialchars($complaint['Details']) ?></p>
                        <p><strong>Location:</strong> <?= htmlspecialchars($complaint['Location']) ?></p>
                        <p><strong>Severity:</strong> <?= htmlspecialchars($complaint['Severity']) ?></p>
                        <p><strong>Status:</strong> 
                            <span class="status <?= strtolower($complaint['Status']) ?>">
                                <?= htmlspecialchars($complaint['Status']) ?>
                            </span>
                        </p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>No complaints submitted yet.</p>
        <?php endif; ?>
    </div>

</div>
<?php include __DIR__ . '/../footer.php'; ?>
</body>
</html>