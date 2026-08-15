<?php
session_start();
include __DIR__ . '/../includes/db.php';

// Only Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 2) {
    header("Location: ../login.php");
    exit();
}

if (!isset($_GET['user_id']) || !isset($_GET['event_id'])) {
    header("Location: view_volunteer_events.php");
    exit();
}

$user_id = $_GET['user_id'];
$event_id = $_GET['event_id'];

$stmt = $pdo->prepare("
    SELECT Proof_Image
    FROM user_participate_volunteer_event
    WHERE U_Id = ? AND Event_Id = ?
");
$stmt->execute([$user_id, $event_id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

$proof_image = $row['Proof_Image'] ?? null;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Volunteer Proof</title>
    <link rel="stylesheet" href="../css/admin_dash.css">
</head>
<body>
    <h1>Volunteer Proof</h1>

    <?php if ($proof_image && file_exists(__DIR__ . '/' . $proof_image)): ?>
    <img src="<?= htmlspecialchars($proof_image) ?>" alt="Volunteer Proof Image" style="max-width:90%; height:auto; display:block; margin:20px auto;">
<?php else: ?>
    <p>Proof image not uploaded yet or file missing.</p>
<?php endif; ?>

    <br>
    <button onclick="history.back()">⬅ Back</button>
</body>
</html>