<?php
session_start();
include __DIR__ . '/../includes/db.php';
include __DIR__ . '/../includes/auth.php';

requireRole(1, '../login.php');
$districts = require __DIR__ . '/../includes/districts.php';

$selectedDistrict = $_GET['district'] ?? '';

if ($selectedDistrict) {
    $stmt = $pdo->prepare("SELECT * FROM truck_schedule WHERE District = ? ORDER BY FIELD(Day_Of_Week,'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'), Pickup_Time");
    $stmt->execute([$selectedDistrict]);
    $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $schedules = [];
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>EcoGuard | Truck Schedule</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="../css/theme.css">
<link rel="stylesheet" href="../css/ecoguard_responsive.css">
</head>
<body>
<?php $path_to_root = "../"; include __DIR__ . '/../header.php'; ?>

<div class="eg-page-header">
    <h1>🚛 Garbage Truck Schedule</h1>
    <p>Find out when the collection truck comes to your area.</p>
</div>

<div class="dashboard">
    <div class="main-content">

        <form method="GET" style="max-width:360px;">
            <label>Select your District</label>
            <select name="district" onchange="this.form.submit()">
                <option value="">-- Choose a district --</option>
                <?php foreach ($districts as $d): ?>
                    <option value="<?= htmlspecialchars($d) ?>" <?= $selectedDistrict === $d ? 'selected' : '' ?>>
                        <?= htmlspecialchars($d) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>

        <?php if ($selectedDistrict): ?>
            <h2 style="margin-top:28px;">Schedule for <?= htmlspecialchars($selectedDistrict) ?></h2>
            <?php if (count($schedules) > 0): ?>
                <table>
                    <tr><th>Day</th><th>Area / Route</th><th>Time</th><th>Waste Type</th><th>Notes</th></tr>
                    <?php foreach ($schedules as $s): ?>
                        <tr>
                            <td><span class="badge-pill"><?= htmlspecialchars($s['Day_Of_Week']) ?></span></td>
                            <td><?= htmlspecialchars($s['Area']) ?></td>
                            <td><?= htmlspecialchars(date('g:i A', strtotime($s['Pickup_Time']))) ?></td>
                            <td><?= htmlspecialchars($s['Waste_Type']) ?></td>
                            <td><?= htmlspecialchars($s['Notes'] ?? '') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php else: ?>
                <p style="margin-top:16px;">No schedule has been published for this district yet. Check back soon, or contact your local authority.</p>
            <?php endif; ?>
        <?php endif; ?>

        <div class="eg-card" style="margin-top:32px;background:var(--eg-mint-pale);border:none;">
            <p style="margin:0;">Missed a pickup, or have bulk waste to dispose of? <a href="request_pickup.php" style="color:var(--eg-primary-dark);font-weight:600;">Request a special pickup →</a></p>
        </div>

    </div>
</div>

<?php include __DIR__ . '/../footer.php'; ?>
</body>
</html>
