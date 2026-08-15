<?php
session_start();
include __DIR__ . '/../includes/db.php';
include __DIR__ . '/../includes/auth.php';
requireRole(2, '../login.php');

$schedules = $pdo->query("SELECT ts.*, u.F_name, u.L_name FROM truck_schedule ts LEFT JOIN users u ON ts.Created_By=u.U_Id ORDER BY ts.District, FIELD(ts.Day_Of_Week,'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'), ts.Pickup_Time")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>EcoGuard | Truck Schedules</title>
<link rel="stylesheet" href="../css/theme.css"><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="../css/ecoguard_responsive.css">
</head>
<body>
<?php include __DIR__ . '/admin_header.php'; ?>
<div class="eg-page-header"><h1>Garbage Truck Schedules</h1><p>Monitor collection schedules published by Local Authorities across districts.</p></div>
<div class="dashboard"><main class="main-content">
<div class="eg-card" style="margin-bottom:18px;background:var(--eg-beige);box-shadow:none;"><strong><?= count($schedules) ?></strong> published schedule entries are currently available.</div>
<?php if ($schedules): ?>
<div class="table-wrap"><table><thead><tr><th>District</th><th>Area / Route</th><th>Day</th><th>Time</th><th>Waste Type</th><th>Published By</th><th>Updated</th></tr></thead><tbody>
<?php foreach ($schedules as $s): ?><tr>
<td><?= htmlspecialchars($s['District']) ?></td><td><?= htmlspecialchars($s['Area']) ?></td><td><?= htmlspecialchars($s['Day_Of_Week']) ?></td><td><?= htmlspecialchars(date('g:i A',strtotime($s['Pickup_Time']))) ?></td><td><?= htmlspecialchars($s['Waste_Type']) ?></td><td><?= htmlspecialchars(trim(($s['F_name']??'').' '.($s['L_name']??'')) ?: 'Unknown') ?></td><td><?= htmlspecialchars($s['Created_At']) ?></td>
</tr><?php endforeach; ?>
</tbody></table></div>
<?php else: ?><div class="eg-empty">No Local Authority truck schedules have been published yet.</div><?php endif; ?>
</main></div>
<?php include __DIR__ . '/admin_footer.php'; ?>
</body></html>
