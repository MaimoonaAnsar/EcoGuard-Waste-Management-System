<?php
session_start();
include __DIR__ . '/../includes/db.php';
include __DIR__ . '/../includes/auth.php';
include __DIR__ . '/../includes/csrf.php';
include __DIR__ . '/../includes/upload.php';

requireRole(1, '../login.php');

$user_id = $_SESSION['user_id'];
$error = "";
$success = "";

if (isset($_POST['submit'])) {
    csrf_verify();

    $details   = trim($_POST['details']);
    $location  = trim($_POST['location']);
    $severity  = $_POST['severity'] ?? 'Normal';
    $latitude  = !empty($_POST['latitude']) ? (float)$_POST['latitude'] : null;
    $longitude = !empty($_POST['longitude']) ? (float)$_POST['longitude'] : null;
    $imagePath = null;

    if (empty($details) || empty($location)) {
        $error = "Please fill all required fields.";
    }

    if (!$error && isset($_FILES['image'])) {
        $result = handleImageUpload($_FILES['image'], __DIR__ . '/../uploads/', 'uploads/');
        if (!$result['ok']) {
            $error = $result['error'];
        } else {
            $imagePath = $result['path'];
        }
    }

    if (!$error) {
        $stmt = $pdo->prepare("INSERT INTO complaint
            (Details, Images, Status, Location, Severity, U_Id, Latitude, Longitude)
            VALUES (?, ?, 'Pending', ?, ?, ?, ?, ?)");

        if ($stmt->execute([$details, $imagePath, $location, $severity, $user_id, $latitude, $longitude])) {
            $success = "Complaint submitted successfully!";
        } else {
            $error = "Something went wrong. Try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>EcoGuard | New Complaint</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="../css/theme.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
</head>
<body>
<?php
$path_to_root = "../";
include __DIR__ . '/../header.php';
?>

<div class="eg-page-header">
    <h1>Submit New Complaint</h1>
    <p>Pin the exact spot on the map so it's easy to locate and act on.</p>
</div>

<div class="dashboard">
    <div class="main-content" style="max-width:720px;margin:0 auto;">

        <?php if ($error): ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
        <?php if ($success): ?><p class="success"><?= htmlspecialchars($success) ?></p><?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="complaint-form" id="complaintForm">
            <?php csrf_field(); ?>

            <label>Location (address / landmark)</label>
            <input type="text" name="location" id="locationInput" placeholder="Search an address, or click the map below" required autocomplete="off">
            <div id="searchResults" style="border:1px solid var(--eg-border); border-radius:8px; margin-top:4px; display:none; background:#fff; max-height:180px; overflow-y:auto;"></div>

            <label>Pin the exact location on the map</label>
            <div id="map" class="eg-map"></div>
            <input type="hidden" name="latitude" id="latitude">
            <input type="hidden" name="longitude" id="longitude">
            <p style="font-size:0.8rem;color:var(--eg-text-muted);margin-top:6px;">Click anywhere on the map, or drag the pin, to set the exact spot.</p>

            <label>Severity</label>
            <select name="severity">
                <option value="Low">Low</option>
                <option value="Normal" selected>Normal</option>
                <option value="High">High</option>
            </select>

            <label>Complaint Details</label>
            <textarea name="details" rows="6" required placeholder="Describe the issue..."></textarea>

            <label>Upload Image (Optional, max 5MB — JPG/PNG/GIF/WEBP)</label>
            <input type="file" name="image" accept="image/*">

            <button type="submit" name="submit" class="block" style="margin-top:24px;">Submit Complaint</button>
        </form>

    </div>
</div>

<?php include __DIR__ . '/../footer.php'; ?>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
// Default center: Colombo, Sri Lanka
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

// Reverse geocoding (map click -> address text) via Nominatim
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

// Address search (typing -> suggestions) via Nominatim
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
    }, 500); // debounce to respect Nominatim's usage policy
});

document.addEventListener('click', (e) => {
    if (e.target !== searchInput) resultsBox.style.display = 'none';
});

// Try to use the citizen's current location as a starting point
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
