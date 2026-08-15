<?php
session_start();
include __DIR__ . '/../includes/db.php';
include __DIR__ . '/../includes/auth.php';
include __DIR__ . '/../includes/csrf.php';
requireRole(4, '../login.php');

$la_id = (int)$_SESSION['user_id'];
$districts = require __DIR__ . '/../includes/districts.php';
$days = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
$error = $success = '';
$edit = null;

if (isset($_POST['save_schedule'])) {
    csrf_verify();
    $id = (int)($_POST['schedule_id'] ?? 0);
    $district = trim($_POST['district'] ?? '');
    $area = trim($_POST['area'] ?? '');
    $day = $_POST['day'] ?? '';
    $time = $_POST['time'] ?? '';
    $waste = trim($_POST['waste_type'] ?? '') ?: 'General';
    $notes = trim($_POST['notes'] ?? '');

    if (!in_array($district, $districts, true) || !$area || !in_array($day, $days, true) || !$time) {
        $error = 'Please complete all required schedule fields.';
    } else {
        if ($id > 0) {
            $stmt = $pdo->prepare("UPDATE truck_schedule SET District=?, Area=?, Day_Of_Week=?, Pickup_Time=?, Waste_Type=?, Notes=? WHERE Schedule_Id=? AND Created_By=?");
            $stmt->execute([$district,$area,$day,$time,$waste,$notes,$id,$la_id]);
            $success = $stmt->rowCount() ? 'Truck schedule updated successfully.' : 'The schedule could not be updated.';
        } else {
            $stmt = $pdo->prepare("INSERT INTO truck_schedule (District,Area,Day_Of_Week,Pickup_Time,Waste_Type,Notes,Created_By) VALUES (?,?,?,?,?,?,?)");
            $stmt->execute([$district,$area,$day,$time,$waste,$notes,$la_id]);
            $success = 'Truck schedule published successfully.';
        }
    }
}

if (isset($_POST['delete_schedule'])) {
    csrf_verify();
    $id = (int)$_POST['delete_schedule'];
    $stmt = $pdo->prepare("DELETE FROM truck_schedule WHERE Schedule_Id=? AND Created_By=?");
    $stmt->execute([$id,$la_id]);
    $success = $stmt->rowCount() ? 'Schedule removed.' : 'Schedule not found or not owned by this authority.';
}

if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM truck_schedule WHERE Schedule_Id=? AND Created_By=?");
    $stmt->execute([(int)$_GET['edit'],$la_id]);
    $edit = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

$stmt = $pdo->prepare("SELECT ts.* FROM truck_schedule ts WHERE ts.Created_By=? ORDER BY ts.District, FIELD(ts.Day_Of_Week,'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'), ts.Pickup_Time");
$stmt->execute([$la_id]);
$schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>EcoGuard | Manage Truck Schedule</title><link rel="stylesheet" href="../css/theme.css"><link rel="stylesheet" href="../css/ecoguard_responsive.css">
</head>
<body>
<?php include 'la_header.php'; ?>
<div class="eg-page-header"><h1>🚛 Garbage Truck Schedule</h1><p>Publish and maintain collection routes so citizens always see the latest information.</p></div>
<div class="dashboard">
<aside class="sidebar"><div class="menu-buttons"><button onclick="location.href='la_dash.php'">← Dashboard</button><button onclick="location.href='la_pickup_requests.php'">Pickup Requests</button><button class="secondary" onclick="location.href='../logout.php'">Logout</button></div></aside>
<main class="main-content">
<?php if($error): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
<?php if($success): ?><div class="success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
<h2><?= $edit ? 'Update Truck Schedule' : 'Publish Truck Schedule' ?></h2>
<form method="POST" class="eg-card" style="box-shadow:none;background:var(--eg-surface-2);">
<?php csrf_field(); ?><input type="hidden" name="schedule_id" value="<?= (int)($edit['Schedule_Id'] ?? 0) ?>">
<div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:4px 18px;">
<div><label>District</label><select name="district" required><option value="">Select district</option><?php foreach($districts as $d): ?><option value="<?= htmlspecialchars($d) ?>" <?= (($edit['District']??'')===$d?'selected':'') ?>><?= htmlspecialchars($d) ?></option><?php endforeach; ?></select></div>
<div><label>Area / Route</label><input name="area" value="<?= htmlspecialchars($edit['Area']??'') ?>" placeholder="e.g. Ward 4, Main Street" required></div>
<div><label>Day</label><select name="day" required><?php foreach($days as $d): ?><option <?= (($edit['Day_Of_Week']??'Monday')===$d?'selected':'') ?>><?= $d ?></option><?php endforeach; ?></select></div>
<div><label>Pickup Time</label><input type="time" name="time" value="<?= htmlspecialchars($edit['Pickup_Time']??'') ?>" required></div>
<div><label>Waste Type</label><select name="waste_type"><?php foreach(['General','Organic','Recyclable','E-Waste'] as $w): ?><option <?= (($edit['Waste_Type']??'General')===$w?'selected':'') ?>><?= $w ?></option><?php endforeach; ?></select></div>
<div><label>Notes</label><input name="notes" value="<?= htmlspecialchars($edit['Notes']??'') ?>" placeholder="Optional collection note"></div>
</div>
<button type="submit" name="save_schedule" style="margin-top:18px;"><?= $edit ? 'Save Changes' : 'Publish Schedule' ?></button>
<?php if($edit): ?><a href="la_schedule.php" style="margin-left:10px;color:var(--eg-primary);font-weight:600;text-decoration:none;">Cancel</a><?php endif; ?>
</form>

<h2 style="margin-top:30px;">My Published Schedules</h2>
<?php if($schedules): ?><div class="table-wrap"><table><thead><tr><th>District</th><th>Area</th><th>Day</th><th>Time</th><th>Waste</th><th>Notes</th><th>Actions</th></tr></thead><tbody>
<?php foreach($schedules as $s): ?><tr><td><?= htmlspecialchars($s['District']) ?></td><td><?= htmlspecialchars($s['Area']) ?></td><td><?= htmlspecialchars($s['Day_Of_Week']) ?></td><td><?= htmlspecialchars(date('g:i A',strtotime($s['Pickup_Time']))) ?></td><td><?= htmlspecialchars($s['Waste_Type']) ?></td><td><?= htmlspecialchars($s['Notes']??'') ?></td><td><div style="display:flex;gap:6px;flex-wrap:wrap"><a class="eg-btn" style="padding:6px 10px;text-decoration:none;font-size:11px" href="?edit=<?= (int)$s['Schedule_Id'] ?>">Edit</a><form method="POST" onsubmit="return confirm('Delete this schedule?')"><?php csrf_field(); ?><input type="hidden" name="delete_schedule" value="<?= (int)$s['Schedule_Id'] ?>"><button class="danger" style="padding:6px 10px;font-size:11px">Delete</button></form></div></td></tr><?php endforeach; ?></tbody></table></div>
<?php else: ?><div class="eg-empty">You have not published a truck schedule yet.</div><?php endif; ?>
</main></div>
<?php include 'la_footer.php'; ?>
</body></html>
