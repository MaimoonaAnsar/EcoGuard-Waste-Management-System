<?php
session_start();
include __DIR__ . '/../includes/db.php';
include __DIR__ . '/../includes/auth.php';
include __DIR__ . '/../includes/csrf.php';

requireRole(1, '../login.php');
$user_id = $_SESSION['user_id'];
$districts = require __DIR__ . '/../includes/districts.php';

$error = "";
$success = "";

if (isset($_POST['submit'])) {
    csrf_verify();

    $district = trim($_POST['district']);
    $address = trim($_POST['address']);
    $waste_type = $_POST['waste_type'] ?? 'General';
    $preferred_date = $_POST['preferred_date'];
    $notes = trim($_POST['notes']);
    $latitude  = !empty($_POST['latitude']) ? (float)$_POST['latitude'] : null;
    $longitude = !empty($_POST['longitude']) ? (float)$_POST['longitude'] : null;

    if (empty($district) || empty($address) || empty($preferred_date)) {
        $error = "Please fill all required fields.";
    } elseif (strtotime($preferred_date) < strtotime(date('Y-m-d'))) {
        $error = "Preferred date can't be in the past.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO pickup_request
            (U_Id, District, Address, Latitude, Longitude, Waste_Type, Preferred_Date, Notes)
            VALUES (?,?,?,?,?,?,?,?)");
        $stmt->execute([$user_id, $district, $address, $latitude, $longitude, $waste_type, $preferred_date, $notes]);
        $success = "Pickup request submitted! You'll be notified once it's scheduled.";
    }
}

$myRequests = $pdo->prepare("SELECT * FROM pickup_request WHERE U_Id = ? ORDER BY Request_Id DESC");
$myRequests->execute([$user_id]);
$myRequests = $myRequests->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>EcoGuard | Request Pickup</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="../css/theme.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
</head>
<body>
<?php $path_to_root = "../"; include __DIR__ . '/../header.php'; ?>

<div class="eg-page-header">
    <h1>📦 Request a Special Pickup</h1>
    <p>For bulk waste or missed collections outside the regular schedule.</p>
</div>

<div class="dashboard">
    <div class="main-content" style="max-width:720px;margin:0 auto;">

        <?php if ($error): ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
        <?php if ($success): ?><p class="success"><?= htmlspecialchars($success) ?></p><?php endif; ?>

        <form method="POST">
            <?php csrf_field(); ?>
            <label>District</label>
            <select name="district" required>
                <option value="">-- Select District --</option>
                <?php foreach ($districts as $d): ?>
                    <option value="<?= htmlspecialchars($d) ?>"><?= htmlspecialchars($d) ?></option>
                <?php endforeach; ?>
            </select>

            <label>Address</label>
            <input type="text" name="address" id="locationInput" placeholder="Search or type your address, or click the map" required autocomplete="off">
            <div id="searchResults" style="border:1px solid var(--eg-border); border-radius:8px; margin-top:4px; display:none; background:#fff; max-height:180px; overflow-y:auto;"></div>

            <div id="map" class="eg-map"></div>
            <input type="hidden" name="latitude" id="latitude">
            <input type="hidden" name="longitude" id="longitude">

            <label>Waste Type</label>
            <select name="waste_type">
                <option value="General">General</option>
                <option value="Bulk / Furniture">Bulk / Furniture</option>
                <option value="Organic">Organic</option>
                <option value="E-Waste">E-Waste</option>
                <option value="Construction Debris">Construction Debris</option>
            </select>

            <label>Preferred Date</label>
            <input type="date" name="preferred_date" min="<?= date('Y-m-d') ?>" required>

            <label>Additional Notes (optional)</label>
            <textarea name="notes" rows="3" placeholder="Any extra details..."></textarea>

            <button type="submit" name="submit" class="block" style="margin-top:20px;">Submit Request</button>
        </form>

        <h2 style="margin-top:36px;">My Pickup Requests</h2>
        <?php if (count($myRequests) > 0): ?>
            <table>
                <tr><th>Date Requested</th><th>Address</th><th>Preferred Date</th><th>Type</th><th>Status</th></tr>
                <?php foreach ($myRequests as $r): ?>
                    <tr>
                        <td><?= htmlspecialchars(date('M j, Y', strtotime($r['Created_At']))) ?></td>
                        <td><?= htmlspecialchars($r['Address']) ?></td>
                        <td><?= htmlspecialchars(date('M j, Y', strtotime($r['Preferred_Date']))) ?></td>
                        <td><?= htmlspecialchars($r['Waste_Type']) ?></td>
                        <td><span class="status <?= strtolower($r['Status']) ?>"><?= htmlspecialchars($r['Status']) ?></span></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php else: ?>
            <p>No pickup requests yet.</p>
        <?php endif; ?>

    </div>
</div>

<?php include __DIR__ . '/../footer.php'; ?>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
const defaultCenter = [6.9271, 79.8612];
const map = L.map('map').setView(defaultCenter, 12);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
    maxZoom: 19
}).addTo(map);

const marker = L.marker(defaultCenter, { draggable: true }).addTo(map);

function setLatLng(lat, lng) {
    document.getElementById("latitude").value = lat;
    document.getElementById("longitude").value = lng;
}
setLatLng(defaultCenter[0], defaultCenter[1]);

marker.on('dragend', () => {
    const pos = marker.getLatLng();
    setLatLng(pos.lat, pos.lng);
    reverseGeocode(pos.lat, pos.lng);
});

map.on('click', (e) => {
    marker.setLatLng(e.latlng);
    setLatLng(e.latlng.lat, e.latlng.lng);
    reverseGeocode(e.latlng.lat, e.latlng.lng);
});

function reverseGeocode(lat, lng) {
    fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`)
        .then(res => res.json())
        .then(data => {
            if (data && data.display_name) {
                document.getElementById("locationInput").value = data.display_name;
            }
        })
        .catch(() => {});
}

const searchInput = document.getElementById("locationInput");
const resultsBox = document.getElementById("searchResults");
let searchTimeout;

searchInput.addEventListener('input', () => {
    clearTimeout(searchTimeout);
    const query = searchInput.value.trim();
    if (query.length < 3) {
        resultsBox.style.display = 'none';
        return;
    }
    searchTimeout = setTimeout(() => {
        fetch(`https://nominatim.openstreetmap.org/search?format=json&limit=5&countrycodes=lk&q=${encodeURIComponent(query)}`)
            .then(res => res.json())
            .then(data => {
                resultsBox.innerHTML = '';
                if (!data.length) { resultsBox.style.display = 'none'; return; }
                data.forEach(place => {
                    const item = document.createElement('div');
                    item.textContent = place.display_name;
                    item.style.padding = '8px 12px';
                    item.style.cursor = 'pointer';
                    item.style.fontSize = '0.85rem';
                    item.addEventListener('mouseover', () => item.style.background = '#f0f7f3');
                    item.addEventListener('mouseout', () => item.style.background = '#fff');
                    item.addEventListener('click', () => {
                        searchInput.value = place.display_name;
                        const lat = parseFloat(place.lat), lng = parseFloat(place.lon);
                        map.setView([lat, lng], 16);
                        marker.setLatLng([lat, lng]);
                        setLatLng(lat, lng);
                        resultsBox.style.display = 'none';
                    });
                    resultsBox.appendChild(item);
                });
                resultsBox.style.display = 'block';
            })
            .catch(() => {});
    }, 500);
});

document.addEventListener('click', (e) => {
    if (e.target !== searchInput) resultsBox.style.display = 'none';
});

if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition((pos) => {
        const here = [pos.coords.latitude, pos.coords.longitude];
        map.setView(here, 15);
        marker.setLatLng(here);
        setLatLng(here[0], here[1]);
    });
}
</script>
</body>
</html>
