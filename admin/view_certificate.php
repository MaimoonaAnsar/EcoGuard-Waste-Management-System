<?php
session_start();
include __DIR__ . '/../includes/db.php';

// Only Admin or Grama Niladhari
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role_id'], [2, 5])) {
    header("Location: ../login.php");
    exit();
}

// Check required GET parameters
if (!isset($_GET['user_id']) || !isset($_GET['event_id'])) {
    header("Location: view_volunteer_events.php");
    exit();
}

$user_id = $_GET['user_id'];
$event_id = $_GET['event_id'];

// Fetch the certificate path
$stmt = $pdo->prepare("
    SELECT Certificate
    FROM user_participate_volunteer_event
    WHERE U_Id = ? AND Event_Id = ?
");
$stmt->execute([$user_id, $event_id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

$certificate = $row['Certificate'] ?? null;
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>View Certificate</title>
    <link rel="stylesheet" href="../css/admin_dash.css">
</head>
<body>

<h1>Volunteer Certificate</h1>

<?php if ($certificate && file_exists(__DIR__ . '/../' . $certificate)): ?>
    <?php
    $ext = strtolower(pathinfo($certificate, PATHINFO_EXTENSION));
    if (in_array($ext, ['jpg','jpeg','png'])): ?>
        <img src="../<?= htmlspecialchars($certificate) ?>" alt="Certificate" style="max-width:90%; height:auto; display:block; margin:20px auto;">
    <?php elseif ($ext === 'pdf'): ?>
        <embed src="../<?= htmlspecialchars($certificate) ?>" type="application/pdf" width="90%" height="600px" />
    <?php else: ?>
        <p>Certificate format not supported.</p>
    <?php endif; ?>
<?php else: ?>
    <p style="color:red;">Certificate not uploaded or file missing.</p>
<?php endif; ?>

<br>
<button onclick="history.back()">⬅ Back</button>

</body>
</html>