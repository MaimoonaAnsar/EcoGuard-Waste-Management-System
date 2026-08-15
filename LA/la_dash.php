<?php
session_start();
include "../includes/db.php";
include "../includes/auth.php";

requireRole(4, '../login.php');

$la_id = $_SESSION['user_id'];

// Fetch authority info
$stmt = $pdo->prepare("SELECT F_name, L_name, Email, Tele FROM users WHERE U_Id = ?");
$stmt->execute([$la_id]);
$la = $stmt->fetch(PDO::FETCH_ASSOC);

$la_name = $la['F_name'] . ' ' . $la['L_name'];

// Fetch ONLY complaints assigned to this Local Authority
$stmt = $pdo->prepare("
    SELECT c.*, u.F_name, u.L_name, u.Email
    FROM complaint c
    JOIN users u ON c.U_Id = u.U_Id
    WHERE c.Assigned_To = ?
    ORDER BY c.Location ASC, c.C_Id ASC
");
$stmt->execute([$la_id]);
$complaints = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>EcoGuard | Local Authority Dashboard</title>
<link rel="stylesheet" href="../css/la_dash2.css">
<link rel="stylesheet" href="../css/theme.css">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="../css/ecoguard_responsive.css">
</head>

<body>
    <?php include 'la_header.php'; ?>


<header>
<h1>EcoGuard Local Authority Panel</h1>
</header>

<div class="dashboard">

<!-- SIDEBAR -->
<div class="sidebar">

<h3><?= htmlspecialchars($la_name) ?></h3>

<p><?= htmlspecialchars($la['Email']) ?></p>

<p><?= htmlspecialchars($la['Tele']) ?></p>

<button onclick="location.href='la_profile.php'">
Edit Profile
</button>

<hr>

<div class="menu-buttons">
<button onclick="location.href='la_schedule.php'">🚛 Manage Truck Schedule</button>
<button onclick="location.href='la_pickup_requests.php'">📦 Pickup Requests</button>
<button class="secondary" onclick="location.href='../logout.php'">Logout</button>
</div>

</div>

<!-- MAIN CONTENT -->

<div class="main-content">

<h2>Complaints Assigned To You</h2>

<?php if (count($complaints) > 0): ?>

<table>

<thead>
<tr>
<th>ID</th>
<th>Citizen</th>
<th>Email</th>
<th>Location</th>
<th>Severity</th>
<th>Status</th>
</tr>
</thead>

<tbody>

<?php foreach ($complaints as $c): ?>

<tr onclick="window.location.href='la_complaint.php?id=<?= $c['C_Id'] ?>'">

<td><?= htmlspecialchars($c['C_Id']) ?></td>
<td><?= htmlspecialchars($c['F_name'] . ' ' . $c['L_name']) ?></td>
<td><?= htmlspecialchars($c['Email']) ?></td>
<td><?= htmlspecialchars($c['Location']) ?></td>
<td><?= htmlspecialchars($c['Severity']) ?></td>
<td>
<span class="status <?= strtolower($c['Status']) ?>">
<?= htmlspecialchars($c['Status']) ?>
</span>
</td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

<?php else: ?>

<p>No complaints assigned to you.</p>

<?php endif; ?>

</div>

</div>

<?php include 'la_footer.php'; ?>
</body>
</html>