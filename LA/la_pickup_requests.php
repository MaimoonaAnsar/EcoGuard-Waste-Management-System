<?php
session_start();
include __DIR__ . '/../includes/db.php';
include __DIR__ . '/../includes/auth.php';
include __DIR__ . '/../includes/csrf.php';

requireRole(4, '../login.php');
$la_id = $_SESSION['user_id'];

$message = "";
if (isset($_POST['update_status'])) {
    csrf_verify();
    $id = (int)$_POST['request_id'];
    $status = $_POST['status'];
    if (in_array($status, ['Pending','Scheduled','Completed','Rejected'], true)) {
        $stmt = $pdo->prepare("UPDATE pickup_request SET Status = ?, Handled_By = ? WHERE Request_Id = ?");
        $stmt->execute([$status, $la_id, $id]);
        $message = "Request #$id updated to $status.";
    }
}

$requests = $pdo->query("
    SELECT pr.*, u.F_name, u.L_name, u.Tele, u.Email
    FROM pickup_request pr
    JOIN users u ON pr.U_Id = u.U_Id
    ORDER BY FIELD(pr.Status,'Pending','Scheduled','Completed','Rejected'), pr.Preferred_Date ASC
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>EcoGuard | Pickup Requests</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="../css/theme.css">
</head>
<body>
<?php include 'la_header.php'; ?>

<div class="eg-page-header">
    <h1>📦 Special Pickup Requests</h1>
    <p>Review and schedule citizen-submitted pickup requests.</p>
</div>

<div class="dashboard">
    <div class="sidebar">
        <div class="menu-buttons">
            <button onclick="location.href='la_dash.php'">⬅ Back to Dashboard</button>
            <button onclick="location.href='la_schedule.php'">Manage Truck Schedule</button>
            <button class="secondary" onclick="location.href='../logout.php'">Logout</button>
        </div>
    </div>

    <div class="main-content">
        <?php if ($message): ?><p class="success"><?= htmlspecialchars($message) ?></p><?php endif; ?>

        <?php if (count($requests) > 0): ?>
            <table>
                <tr>
                    <th>ID</th><th>Citizen</th><th>Address</th><th>District</th>
                    <th>Preferred Date</th><th>Type</th><th>Status</th><th>Update</th>
                </tr>
                <?php foreach ($requests as $r): ?>
                    <tr>
                        <td><?= (int)$r['Request_Id'] ?></td>
                        <td><?= htmlspecialchars($r['F_name'] . ' ' . $r['L_name']) ?><br>
                            <span style="color:var(--eg-text-muted);font-size:0.8rem;"><?= htmlspecialchars($r['Tele']) ?></span></td>
                        <td><?= htmlspecialchars($r['Address']) ?></td>
                        <td><?= htmlspecialchars($r['District']) ?></td>
                        <td><?= htmlspecialchars(date('M j, Y', strtotime($r['Preferred_Date']))) ?></td>
                        <td><?= htmlspecialchars($r['Waste_Type']) ?></td>
                        <td><span class="status <?= strtolower($r['Status']) ?>"><?= htmlspecialchars($r['Status']) ?></span></td>
                        <td>
                            <form method="POST" style="display:flex;gap:6px;">
                                <?php csrf_field(); ?>
                                <input type="hidden" name="request_id" value="<?= (int)$r['Request_Id'] ?>">
                                <select name="status" style="padding:6px 8px;">
                                    <?php foreach (['Pending','Scheduled','Completed','Rejected'] as $s): ?>
                                        <option value="<?= $s ?>" <?= $r['Status']===$s?'selected':'' ?>><?= $s ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" name="update_status" style="padding:6px 12px;font-size:0.78rem;">Save</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php else: ?>
            <p>No pickup requests yet.</p>
        <?php endif; ?>
    </div>
</div>

<?php include 'la_footer.php'; ?>
</body>
</html>
