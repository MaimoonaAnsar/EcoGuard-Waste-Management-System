<?php
session_start();
include "../includes/db.php";

// Only Divisional Secretary
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 3) {
    header("Location: ../login.php");
    exit();
}

$ds_id = $_SESSION['user_id'];

// Fetch DS info
$stmt = $pdo->prepare("SELECT F_name, L_name, Email, Tele FROM users WHERE U_Id = ?");
$stmt->execute([$ds_id]);
$ds = $stmt->fetch(PDO::FETCH_ASSOC);

$ds_name = $ds['F_name'] . ' ' . $ds['L_name'];

// Fetch ONLY complaints assigned/escalated to this DS
$stmt = $pdo->prepare("
    SELECT c.*, u.F_name, u.L_name, u.Email
    FROM complaint c
    JOIN users u ON c.U_Id = u.U_Id
    WHERE c.Assigned_To = ?
    ORDER BY c.Location ASC, c.C_Id ASC
");
$stmt->execute([$ds_id]);
$complaints = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title>EcoGuard | Divisional Secretariat Dashboard</title>
<link rel="stylesheet" href="../css/ds_dash2.css">
<link rel="stylesheet" href="../css/ecoguard_responsive.css">
</head>

<body>
     <?php include 'ds_header.php'; ?>

<header>
<h1>EcoGuard Divisional Secretariat Panel</h1>
</header>

<div class="dashboard">

<div class="sidebar">
<h3><?= htmlspecialchars($ds_name) ?></h3>
<p><?= htmlspecialchars($ds['Email']) ?></p>
<p><?= htmlspecialchars($ds['Tele']) ?></p>

<button onclick="location.href='ds_profile.php'">Edit Profile</button>
<hr>
<button onclick="location.href='../logout.php'">Logout</button>
<button type="button" onclick="location.href='ds_pickup_requests.php'">
    🚛 Special Pickup Requests
</button>
</div>



<div class="main-content">
<h2>Complaints Assigned To You</h2>

<?php if (!empty($complaints)): ?>
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
<tr onclick="window.location.href='ds_complaint.php?id=<?= $c['C_Id'] ?>'">
<td><?= htmlspecialchars($c['C_Id']) ?></td>
<td><?= htmlspecialchars($c['F_name'] . ' ' . $c['L_name']) ?></td>
<td><?= htmlspecialchars($c['Email']) ?></td>
<td><?= htmlspecialchars($c['Location']) ?></td>
<td><?= htmlspecialchars($c['Severity']) ?></td>
<td>
<span class="status <?= strtolower(str_replace(' ', '-', $c['Status'])) ?>">
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

 <?php include 'ds_footer.php'; ?>
</body>
</html>

