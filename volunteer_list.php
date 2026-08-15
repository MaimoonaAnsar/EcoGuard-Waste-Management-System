<?php
session_start();
include "includes/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

/*
Get user area
*/
$stmtUser = $pdo->prepare("SELECT area_id FROM users WHERE id = ?");
$stmtUser->execute([$user_id]);
$user = $stmtUser->fetch();

$area_id = $user['area_id'];

/*
Get opportunities in area
*/
$stmt = $pdo->prepare("
    SELECT * FROM volunteering_opportunities
    WHERE area_id = ?
    ORDER BY date ASC
");
$stmt->execute([$area_id]);
$events = $stmt->fetchAll();
?>

<h2>Volunteer Opportunities In Your Area</h2>

<?php foreach($events as $e): ?>
<div style="border:1px solid #ccc; padding:10px; margin:10px;">
    <h3><?= htmlspecialchars($e['title']) ?></h3>
    <p>Location: <?= htmlspecialchars($e['location']) ?></p>
    <p>Date: <?= $e['date'] ?></p>

    <?php if ($role == "citizen"): ?>
        <button>Join</button>
    <?php endif; ?>

    <?php if ($role == "gn" || $role == "admin"): ?>
        <button>Edit</button>
    <?php endif; ?>
</div>
<?php endforeach; ?>
