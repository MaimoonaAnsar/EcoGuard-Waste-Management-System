<?php
session_start();
include "../includes/db.php";

// Only Citizen
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if (!isset($_GET['id'])) {
    header("Location: citizen_dash.php");
    exit();
}

$complaint_id = $_GET['id'];

// Fetch complaint ONLY if it belongs to this citizen
$stmt = $pdo->prepare("
SELECT * FROM complaint
WHERE C_Id = ? AND U_Id = ?
");
$stmt->execute([$complaint_id, $user_id]);
$complaint = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$complaint) {
    echo "Complaint not found.";
    exit();
}

/* FETCH NOTES ADDED BY AUTHORITIES */
$stmt = $pdo->prepare("
SELECT n.*, u.F_name, u.L_name
FROM complaint_notes n
JOIN users u ON n.User_Id = u.U_Id
WHERE n.C_Id = ?
ORDER BY n.Created_At DESC
");
$stmt->execute([$complaint_id]);
$notes = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html>
<head>
<title>Complaint Details</title>
<link rel="stylesheet" href="../css/citizen_complaint2.css">
</head>

<body>
<?php 
$path_to_root = "../";
include __DIR__ . '/../header.php'; 
?>

<div class="container">

<h2>Complaint #<?= htmlspecialchars($complaint['C_Id']) ?></h2>

<p><b>Location:</b> <?= htmlspecialchars($complaint['Location']) ?></p>

<p><b>Severity:</b> <?= htmlspecialchars($complaint['Severity']) ?></p>

<p><b>Status:</b>
<span class="status <?= strtolower($complaint['Status']) ?>">
<?= htmlspecialchars($complaint['Status']) ?>
</span>
</p>

<p><b>Details:</b></p>
<p><?= nl2br(htmlspecialchars($complaint['Details'])) ?></p>

<hr>

<h3>Authority Updates</h3>

<?php if (!empty($notes)): ?>

<?php foreach ($notes as $n): ?>

<p>
<b><?= htmlspecialchars($n['F_name']." ".$n['L_name']) ?></b><br>
<?= htmlspecialchars($n['Note']) ?><br>
<small><?= $n['Created_At'] ?></small>
</p>

<hr>

<?php endforeach; ?>

<?php else: ?>

<p>No updates from authorities yet.</p>

<?php endif; ?>

<br>

</div>

<?php include __DIR__ . '/../footer.php'; ?>
</body>
</html>