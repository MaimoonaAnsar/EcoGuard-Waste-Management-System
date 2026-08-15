<?php
session_start();
include __DIR__ . '/../includes/db.php';
include __DIR__ . '/../includes/auth.php';
requireRole(1, '../login.php');

$user_id = (int)$_SESSION['user_id'];

$stmtUser = $pdo->prepare("SELECT * FROM users WHERE U_Id = ?");
$stmtUser->execute([$user_id]);
$user = $stmtUser->fetch(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT * FROM complaint WHERE U_Id = ? ORDER BY C_Id DESC");
$stmt->execute([$user_id]);
$complaints = $stmt->fetchAll(PDO::FETCH_ASSOC);

$events = $pdo->query("SELECT * FROM volunteer_event ORDER BY Date ASC")->fetchAll(PDO::FETCH_ASSOC);

// The citizen can choose a district. Published LA schedules are shown beside the map.
$districts = require __DIR__ . '/../includes/districts.php';
$selectedDistrict = $_GET['district'] ?? '';
$schedules = [];
$scheduleTableReady = true;
if ($selectedDistrict && in_array($selectedDistrict, $districts, true)) {
    $scheduleStmt = $pdo->prepare("SELECT ts.*, u.F_name, u.L_name FROM truck_schedule ts LEFT JOIN users u ON ts.Created_By = u.U_Id WHERE ts.District = ? ORDER BY FIELD(ts.Day_Of_Week,'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'), ts.Pickup_Time");
    try {
        $scheduleStmt->execute([$selectedDistrict]);
        $schedules = $scheduleStmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $scheduleTableReady = false;
        error_log('EcoGuard truck_schedule unavailable: ' . $e->getMessage());
    }
}

// Complaint markers for the Leaflet map.
$markers = array_values(array_filter(array_map(function($c) {
    if (!isset($c['Latitude'], $c['Longitude']) || $c['Latitude'] === null || $c['Longitude'] === null) return null;
    return [
        'id' => (int)$c['C_Id'],
        'lat' => (float)$c['Latitude'],
        'lng' => (float)$c['Longitude'],
        'location' => $c['Location'],
        'status' => $c['Status']
    ];
}, $complaints)));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>EcoGuard | Citizen Dashboard</title>
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<link rel="stylesheet" href="../css/theme.css">
<link rel="stylesheet" href="../css/ecoguard_responsive.css">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
</head>
<body>
<?php include __DIR__ . '/../header.php'; ?>

<div class="eg-page-header">
    <h1>Citizen Dashboard</h1>
    <p>Keep your community clean, track complaints and follow your collection schedule.</p>
</div>

<div class="dashboard">
    <aside class="sidebar">
        <div class="profile-box">
            <h3><?= htmlspecialchars($user['F_name'] ?? 'Citizen') ?></h3>
            <p><?= htmlspecialchars($user['Email'] ?? '') ?></p>
            <p><?= htmlspecialchars($user['Tele'] ?? '') ?></p>
            <button type="button" onclick="location.href='edit_profile.php'" style="margin-top:12px;width:100%;">Edit Profile</button>
        </div>
        <hr>
        <div class="menu-buttons">
            <button onclick="location.href='new_complaint.php'">📝 New Complaint</button>
            <button onclick="location.href='history.php'">📜 View History</button>
            <button onclick="location.href='truck_schedule.php'">🚛 Truck Schedule</button>
            <button onclick="location.href='request_pickup.php'">📦 Request Pickup</button>
            <button onclick="location.href='give_feedback.php'">💬 Give Feedback</button>

            <button class="secondary" onclick="location.href='../logout.php'">Logout</button>
        </div>
    </aside>

    <main class="main-content">
      
        <section class="eg-dashboard-grid">
            <div class="eg-panel">
                <h2>Complaint Locations</h2>
                <p class="eg-panel-subtitle">Your submitted complaints with a saved map location.</p>
                <div id="citizenMap" class="eg-map"></div>
            </div>

            <div class="eg-panel">
                <h2>🚛 Collection Schedule</h2>
                <p class="eg-panel-subtitle">Choose your district to see schedules published by the Local Authority.</p>
                <form method="GET">
                    <label for="district">District</label>
                    <select id="district" name="district" onchange="this.form.submit()">
                        <option value="">Choose district</option>
                        <?php foreach ($districts as $d): ?>
                            <option value="<?= htmlspecialchars($d) ?>" <?= $selectedDistrict === $d ? 'selected' : '' ?>><?= htmlspecialchars($d) ?></option>
                        <?php endforeach; ?>
                    </select>
                </form>
                <div class="eg-schedule-list" style="margin-top:14px;">
                    <?php if (!$scheduleTableReady): ?>
                        <div class="eg-empty">Truck scheduling is not available yet. Please run <strong>sql/migration_2026.sql</strong> once on the EcoGuard database.</div>
                    <?php elseif (!$selectedDistrict): ?>
                        <div class="eg-empty">Select your district to view the latest truck collection times.</div>
                    <?php elseif (!$schedules): ?>
                        <div class="eg-empty">No published schedule for <?= htmlspecialchars($selectedDistrict) ?> yet.</div>
                    <?php else: ?>
                        <?php foreach ($schedules as $s): ?>
                            <div class="eg-schedule-item">
                                <strong><?= htmlspecialchars($s['Area']) ?></strong>
                                <div class="eg-schedule-meta">
                                    <span class="eg-chip"><?= htmlspecialchars($s['Day_Of_Week']) ?></span>
                                    <span class="eg-chip"><?= htmlspecialchars(date('g:i A', strtotime($s['Pickup_Time']))) ?></span>
                                    <span class="eg-chip"><?= htmlspecialchars($s['Waste_Type']) ?></span>
                                </div>
                                <?php if (!empty($s['Notes'])): ?><small style="display:block;color:var(--eg-text-muted);margin-top:6px;"><?= htmlspecialchars($s['Notes']) ?></small><?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <a class="eg-btn" style="display:block;text-align:center;text-decoration:none;margin-top:12px;" href="truck_schedule.php<?= $selectedDistrict ? '?district='.urlencode($selectedDistrict) : '' ?>">View Full Schedule</a>
            </div>
        </section>

        <section>
            <h2>Your Complaint History</h2>
            <?php if ($complaints): ?>
                <div class="table-wrap">
                    <table>
                        <thead><tr><th>ID</th><th>Details</th><th>Location</th><th>Severity</th><th>Status</th></tr></thead>
                        <tbody>
                        <?php foreach ($complaints as $row): ?>
                            <tr onclick="window.location.href='citizen_complaint.php?id=<?= (int)$row['C_Id'] ?>'">
                                <td><?= (int)$row['C_Id'] ?></td>
                                <td><?= htmlspecialchars(mb_strimwidth($row['Details'],0,55,'...')) ?></td>
                                <td><?= htmlspecialchars($row['Location']) ?></td>
                                <td><?= htmlspecialchars($row['Severity']) ?></td>
                                <td><span class="status <?= strtolower(preg_replace('/[^a-z]/','',$row['Status'])) ?>"><?= htmlspecialchars($row['Status']) ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?><div class="eg-empty">You have not submitted any complaints yet.</div><?php endif; ?>
        </section>

   

        <section class="volunteer-section" style="margin-top:30px;">
            <h2>Upcoming Volunteer Opportunities</h2>
            <?php if ($events): ?>
                <div class="event-grid">
                    <?php foreach ($events as $event): ?>
                        <?php $imgPath = !empty($event['Image']) ? '../' . $event['Image'] : ''; ?>
                        <div class="event-block" onclick="location.href='volunteer_details.php?id=<?= (int)$event['Event_Id'] ?>'">
                            <?php if ($imgPath): ?><div class="event-image"><img src="<?= htmlspecialchars($imgPath) ?>" alt="<?= htmlspecialchars($event['Name']) ?>"></div><?php endif; ?>
                            <h4 style="padding:0 14px;margin-bottom:6px;"><?= htmlspecialchars($event['Name']) ?></h4>
                            <p><strong>Date:</strong> <?= htmlspecialchars($event['Date']) ?></p>
                            <p><strong>Time:</strong> <?= htmlspecialchars($event['Starting_Time']) ?></p>
                            <p><strong>Location:</strong> <?= htmlspecialchars($event['Location']) ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?><div class="eg-empty">No volunteer events available.</div><?php endif; ?>

             <div style="margin-top:18px;"><button onclick="location.href='my_certificates.php'">View My Certificates</button></div>
        </section>
    </main>
</div>

<?php include __DIR__ . '/../footer.php'; ?>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
const complaintMarkers = <?= json_encode($markers, JSON_UNESCAPED_SLASHES) ?>;
const map = L.map('citizenMap', {scrollWheelZoom:false}).setView([6.9271,79.8612], 10);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{maxZoom:19,attribution:'&copy; OpenStreetMap contributors'}).addTo(map);
const group = L.featureGroup();
complaintMarkers.forEach(c => {
    const marker = L.marker([c.lat,c.lng]).bindPopup('<strong>Complaint #'+c.id+'</strong><br>'+escapeHtml(c.location)+'<br><span>'+escapeHtml(c.status)+'</span>');
    marker.addTo(group);
});
if (complaintMarkers.length) { group.addTo(map); map.fitBounds(group.getBounds().pad(.18)); }
function escapeHtml(value){const d=document.createElement('div');d.textContent=value??'';return d.innerHTML;}
setTimeout(()=>map.invalidateSize(),250);
window.addEventListener('resize',()=>map.invalidateSize());
</script>
</body>
</html>
