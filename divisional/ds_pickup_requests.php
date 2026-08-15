<?php
session_start();
include "../includes/db.php";

/* =========================================================
   ONLY DIVISIONAL SECRETARY
   ========================================================= */
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 3) {
    header("Location: ../login.php");
    exit();
}

$ds_id = $_SESSION['user_id'];


/* =========================================================
   FETCH DS INFORMATION
   ========================================================= */
$stmt = $pdo->prepare("
    SELECT F_name, L_name, Email, Tele
    FROM users
    WHERE U_Id = ?
");
$stmt->execute([$ds_id]);

$ds = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$ds) {
    die("DS user not found.");
}

$ds_name = $ds['F_name'] . ' ' . $ds['L_name'];


/* =========================================================
   HANDLE PICKUP REQUEST ACTIONS
   ========================================================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $request_id = isset($_POST['request_id'])
        ? (int) $_POST['request_id']
        : 0;

    $action = $_POST['action'] ?? '';

    if ($request_id > 0) {

        if ($action === 'schedule') {

            $stmt = $pdo->prepare("
                UPDATE pickup_request
                SET Status = 'Scheduled',
                    Handled_By = ?
                WHERE Request_Id = ?
            ");

            $stmt->execute([
                $ds_id,
                $request_id
            ]);

        } elseif ($action === 'reject') {

            $stmt = $pdo->prepare("
                UPDATE pickup_request
                SET Status = 'Rejected',
                    Handled_By = ?
                WHERE Request_Id = ?
            ");

            $stmt->execute([
                $ds_id,
                $request_id
            ]);

        } elseif ($action === 'complete') {

            $stmt = $pdo->prepare("
                UPDATE pickup_request
                SET Status = 'Completed',
                    Handled_By = ?
                WHERE Request_Id = ?
            ");

            $stmt->execute([
                $ds_id,
                $request_id
            ]);
        }
    }

    header(
        "Location: ds_pickup_requests.php?request=" .
        $request_id .
        "&updated=1"
    );

    exit();
}


/* =========================================================
   FETCH PICKUP REQUESTS
   ========================================================= */
$stmt = $pdo->prepare("
    SELECT
        p.Request_Id,
        p.U_Id,
        p.District,
        p.Address,
        p.Latitude,
        p.Longitude,
        p.Waste_Type,
        p.Preferred_Date,
        p.Notes,
        p.Status,
        p.Handled_By,
        p.Created_At,

        u.F_name,
        u.L_name,
        u.Email,
        u.Tele

    FROM pickup_request p

    LEFT JOIN users u
        ON p.U_Id = u.U_Id

    ORDER BY
        CASE
            WHEN p.Status = 'Pending' THEN 1
            WHEN p.Status = 'Scheduled' THEN 2
            WHEN p.Status = 'Completed' THEN 3
            WHEN p.Status = 'Rejected' THEN 4
            ELSE 5
        END,

        p.Created_At DESC
");

$stmt->execute();

$pickup_requests = $stmt->fetchAll(PDO::FETCH_ASSOC);


/* =========================================================
   SELECT REQUEST
   ========================================================= */
$selected_request = null;

if (isset($_GET['request']) && is_numeric($_GET['request'])) {

    $selected_id = (int) $_GET['request'];

    foreach ($pickup_requests as $pickup) {

        if ((int) $pickup['Request_Id'] === $selected_id) {

            $selected_request = $pickup;
            break;
        }
    }
}


/* Automatically select first request */
if (!$selected_request && !empty($pickup_requests)) {
    $selected_request = $pickup_requests[0];
}


/* =========================================================
   MAP LOCATION
   ========================================================= */
$latitude = 6.9271;
$longitude = 80.7789;

if ($selected_request) {

    if (
        is_numeric($selected_request['Latitude']) &&
        is_numeric($selected_request['Longitude'])
    ) {

        $latitude = (float) $selected_request['Latitude'];
        $longitude = (float) $selected_request['Longitude'];
    }
}


/* =========================================================
   STATUS CLASS
   ========================================================= */
function statusClass($status)
{
    return strtolower(
        preg_replace(
            '/[^a-zA-Z0-9]+/',
            '-',
            $status
        )
    );
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>EcoGuard | Special Pickup Requests</title>


<link rel="stylesheet"
      href="../css/ds_dash2.css">

<link rel="stylesheet"
      href="../css/ecoguard_responsive.css">


<!-- LEAFLET -->

<link rel="stylesheet"
      href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">


<style>

/* =========================================================
   SPECIAL PICKUP PAGE
   ========================================================= */

.pickup-page {

    padding: 12px 15px;

}


/* =========================================================
   PAGE HEADER
   ========================================================= */

.pickup-page-header {

    display: flex;

    justify-content: space-between;

    align-items: center;

    margin-bottom: 12px;

}

.pickup-page-header h2 {

    margin: 0;

    font-size: 20px;

}

.pickup-page-header p {

    margin: 3px 0 0;

    color: #666;

    font-size: 12px;

}


/* =========================================================
   UPDATED MESSAGE
   ========================================================= */

.updated-message {

    background: #d4edda;

    color: #155724;

    padding: 7px 10px;

    border-radius: 7px;

    margin-bottom: 10px;

    font-size: 12px;

}


/* =========================================================
   REQUEST TABLE
   ========================================================= */

.requests-card {

    background: white;

    border-radius: 11px;

    padding: 12px 14px;

    margin-bottom: 13px;

    box-shadow:
        0 3px 10px rgba(0,0,0,0.06);

}

.requests-card h3 {

    margin: 0 0 7px;

    font-size: 16px;

}

.table-wrapper {

    width: 100%;

    overflow-x: auto;

}

.requests-table {

    width: 100%;

    border-collapse: collapse;

    min-width: 700px;

}

.requests-table th,
.requests-table td {

    padding: 7px 9px;

    border-bottom:
        1px solid #e8e8e8;

    text-align: left;

    font-size: 11px;

}

.requests-table th {

    background: #eef5ec;

    color: #385b38;

}

.requests-table tr:hover {

    background: #f7faf6;

}

.requests-table tr.selected {

    background: #e9f3e6;

}


/* =========================================================
   STATUS
   ========================================================= */

.status {

    display: inline-block;

    padding: 3px 7px;

    border-radius: 15px;

    font-size: 10px;

    font-weight: 600;

}

.status.pending {

    background: #fff3cd;

    color: #856404;

}

.status.scheduled {

    background: #d1ecf1;

    color: #0c5460;

}

.status.completed {

    background: #d4edda;

    color: #155724;

}

.status.rejected {

    background: #f8d7da;

    color: #721c24;

}


/* =========================================================
   VIEW BUTTON
   ========================================================= */

.view-request {

    display: inline-block;

    padding: 4px 8px;

    background: #557a55;

    color: white;

    text-decoration: none;

    border-radius: 6px;

    font-size: 10px;

}

.view-request:hover {

    background: #3f613f;

}


/* =========================================================
   DETAILS + MAP
   ========================================================= */

.request-layout {

    display: grid;

    grid-template-columns:
        minmax(0, 1fr)
        minmax(0, 1fr);

    gap: 13px;

}


/* =========================================================
   CARDS
   ========================================================= */

.request-details-card,
.map-card {

    background: white;

    border-radius: 11px;

    padding: 15px;

    box-shadow:
        0 3px 10px rgba(0,0,0,0.06);

}

.request-details-card h3,
.map-card h3 {

    margin-top: 0;

    margin-bottom: 7px;

    color: #385b38;

    font-size: 16px;

}


/* =========================================================
   DETAILS
   ========================================================= */

.detail-row {

    display: grid;

    grid-template-columns:
        110px
        minmax(0, 1fr);

    gap: 8px;

    padding: 5px 0;

    border-bottom:
        1px solid #eeeeee;

}

.detail-label {

    font-weight: 600;

    color: #555;

    font-size: 11px;

}

.detail-value {

    color: #222;

    font-size: 11px;

    overflow-wrap: anywhere;

}


/* =========================================================
   NOTES
   ========================================================= */

.notes-title {

    margin-top: 10px !important;

}

.notes-box {

    background: #f7f9f5;

    border-radius: 8px;

    padding: 8px 10px;

    min-height: 35px;

    white-space: pre-wrap;

    overflow-wrap: anywhere;

    font-size: 11px;

}


/* =========================================================
   ACTION BUTTONS
   ========================================================= */

.action-area {

    display: flex;

    flex-wrap: wrap;

    gap: 6px;

    margin-top: 10px;

}

.action-area form {

    margin: 0;

}

.action-btn {

    border: none;

    padding: 7px 10px;

    border-radius: 6px;

    color: white;

    cursor: pointer;

    font-weight: 600;

    font-size: 10px;

}

.schedule-btn {

    background: #557a55;

}

.reject-btn {

    background: #b84a4a;

}

.complete-btn {

    background: #3f7d4f;

}

.action-btn:hover {

    opacity: 0.9;

}


/* =========================================================
   MAP
   ========================================================= */

#pickupMap {

    width: 100%;

    height: 330px;

    border-radius: 9px;

    overflow: hidden;

}

.map-address {

    margin-top: 6px;

    color: #666;

    font-size: 10px;

}


/* =========================================================
   EMPTY STATE
   ========================================================= */

.empty-state {

    background: white;

    border-radius: 11px;

    padding: 30px;

    text-align: center;

    color: #666;

}


/* =========================================================
   RESPONSIVE
   ========================================================= */

@media (max-width: 950px) {

    .request-layout {

        grid-template-columns: 1fr;

    }

    #pickupMap {

        height: 300px;

    }

}


@media (max-width: 600px) {

    .pickup-page {

        padding: 10px;

    }

    .request-details-card,
    .map-card,
    .requests-card {

        padding: 12px;

    }

    .detail-row {

        grid-template-columns: 1fr;

        gap: 3px;

    }

    #pickupMap {

        height: 270px;

    }

    .action-area {

        flex-direction: column;

    }

    .action-area form {

        width: 100%;

    }

    .action-btn {

        width: 100%;

    }

}

</style>

</head>


<body>


<?php include 'ds_header.php'; ?>


<!-- =====================================================
     SPECIAL PICKUP CONTENT ONLY
     ===================================================== -->

<div class="main-content pickup-page">


<div class="pickup-page-header">

    <div>

        <h2>
            Special Pickup Requests
        </h2>

        <p>
            Manage special pickup requests submitted by citizens.
        </p>

    </div>

</div>


<?php if (isset($_GET['updated'])): ?>

<div class="updated-message">

    Pickup request status updated successfully.

</div>

<?php endif; ?>


<?php if (!empty($pickup_requests)): ?>


<!-- =====================================================
     REQUEST LIST
     ===================================================== -->

<div class="requests-card">

<h3>
    Received Requests
</h3>


<div class="table-wrapper">

<table class="requests-table">

<thead>

<tr>

<th>ID</th>

<th>Citizen</th>

<th>District</th>

<th>Waste Type</th>

<th>Preferred Date</th>

<th>Status</th>

<th>Action</th>

</tr>

</thead>


<tbody>

<?php foreach ($pickup_requests as $pickup): ?>

<tr class="<?=
    (
        $selected_request &&
        $selected_request['Request_Id'] ==
        $pickup['Request_Id']
    )
    ? 'selected'
    : ''
?>">


<td>

#<?= htmlspecialchars(
    $pickup['Request_Id']
) ?>

</td>


<td>

<?= htmlspecialchars(
    trim(
        $pickup['F_name'] .
        ' ' .
        $pickup['L_name']
    )
) ?>

</td>


<td>

<?= htmlspecialchars(
    $pickup['District']
) ?>

</td>


<td>

<?= htmlspecialchars(
    $pickup['Waste_Type']
) ?>

</td>


<td>

<?= htmlspecialchars(
    $pickup['Preferred_Date']
) ?>

</td>


<td>

<span class="status <?= htmlspecialchars(
    statusClass(
        $pickup['Status']
    )
) ?>">

<?= htmlspecialchars(
    $pickup['Status']
) ?>

</span>

</td>


<td>

<a
    class="view-request"
    href="?request=<?= urlencode(
        $pickup['Request_Id']
    ) ?>"
>

View

</a>

</td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

</div>

</div>


<!-- =====================================================
     SELECTED REQUEST
     ===================================================== -->

<?php if ($selected_request): ?>


<div class="request-layout">


<!-- =====================================================
     LEFT — REQUEST DETAILS
     ===================================================== -->

<div class="request-details-card">

<h3>

Request Details
#<?= htmlspecialchars(
    $selected_request['Request_Id']
) ?>

</h3>


<div class="detail-row">

<div class="detail-label">
    Citizen
</div>

<div class="detail-value">

<?= htmlspecialchars(
    trim(
        $selected_request['F_name'] .
        ' ' .
        $selected_request['L_name']
    )
) ?>

</div>

</div>


<div class="detail-row">

<div class="detail-label">
    Email
</div>

<div class="detail-value">

<?= htmlspecialchars(
    $selected_request['Email'] ?? '-'
) ?>

</div>

</div>


<div class="detail-row">

<div class="detail-label">
    Telephone
</div>

<div class="detail-value">

<?= htmlspecialchars(
    $selected_request['Tele'] ?? '-'
) ?>

</div>

</div>


<div class="detail-row">

<div class="detail-label">
    District
</div>

<div class="detail-value">

<?= htmlspecialchars(
    $selected_request['District']
) ?>

</div>

</div>


<div class="detail-row">

<div class="detail-label">
    Address
</div>

<div class="detail-value">

<?= htmlspecialchars(
    $selected_request['Address']
) ?>

</div>

</div>


<div class="detail-row">

<div class="detail-label">
    Waste Type
</div>

<div class="detail-value">

<?= htmlspecialchars(
    $selected_request['Waste_Type']
) ?>

</div>

</div>


<div class="detail-row">

<div class="detail-label">
    Preferred Date
</div>

<div class="detail-value">

<?= htmlspecialchars(
    $selected_request['Preferred_Date']
) ?>

</div>

</div>


<div class="detail-row">

<div class="detail-label">
    Status
</div>

<div class="detail-value">

<span class="status <?= htmlspecialchars(
    statusClass(
        $selected_request['Status']
    )
) ?>">

<?= htmlspecialchars(
    $selected_request['Status']
) ?>

</span>

</div>

</div>


<div class="detail-row">

<div class="detail-label">
    Submitted
</div>

<div class="detail-value">

<?= htmlspecialchars(
    $selected_request['Created_At']
) ?>

</div>

</div>


<h3 class="notes-title">

Citizen Notes

</h3>


<div class="notes-box">

<?= !empty($selected_request['Notes'])
    ? htmlspecialchars(
        $selected_request['Notes']
    )
    : 'No notes provided.'
?>

</div>


<!-- =====================================================
     ACTIONS
     ===================================================== -->

<div class="action-area">


<?php if (
    $selected_request['Status'] === 'Pending'
): ?>


<form method="POST">

<input
    type="hidden"
    name="request_id"
    value="<?= htmlspecialchars(
        $selected_request['Request_Id']
    ) ?>"
>

<input
    type="hidden"
    name="action"
    value="schedule"
>


<button
    type="submit"
    class="action-btn schedule-btn"
    onclick="return confirm(
        'Schedule this pickup request?'
    );"
>

✓ Schedule Pickup

</button>

</form>


<form method="POST">

<input
    type="hidden"
    name="request_id"
    value="<?= htmlspecialchars(
        $selected_request['Request_Id']
    ) ?>"
>

<input
    type="hidden"
    name="action"
    value="reject"
>


<button
    type="submit"
    class="action-btn reject-btn"
    onclick="return confirm(
        'Reject this pickup request?'
    );"
>

✕ Reject Request

</button>

</form>


<?php elseif (
    $selected_request['Status'] === 'Scheduled'
): ?>


<form method="POST">

<input
    type="hidden"
    name="request_id"
    value="<?= htmlspecialchars(
        $selected_request['Request_Id']
    ) ?>"
>

<input
    type="hidden"
    name="action"
    value="complete"
>


<button
    type="submit"
    class="action-btn complete-btn"
    onclick="return confirm(
        'Mark this pickup as completed?'
    );"
>

✓ Mark as Completed

</button>

</form>


<?php endif; ?>


</div>

</div>


<!-- =====================================================
     RIGHT — MAP
     ===================================================== -->

<div class="map-card">

<h3>

Pickup Location

</h3>


<div id="pickupMap"></div>


<div class="map-address">

📍
<?= htmlspecialchars(
    $selected_request['Address']
) ?>

</div>

</div>


</div>


<?php endif; ?>


<?php else: ?>


<div class="empty-state">

<h3>
    No Special Pickup Requests
</h3>

<p>
    There are currently no special pickup requests
    submitted by citizens.
</p>

</div>


<?php endif; ?>


</div>


<?php include 'ds_footer.php'; ?>


<!-- =====================================================
     LEAFLET JS
     ===================================================== -->

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>


<?php if ($selected_request): ?>

<script>

document.addEventListener("DOMContentLoaded", function () {

    const latitude =
        <?= json_encode($latitude) ?>;

    const longitude =
        <?= json_encode($longitude) ?>;


    const map =
        L.map("pickupMap").setView(
            [latitude, longitude],
            15
        );


    L.tileLayer(
        "https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png",
        {
            maxZoom: 19,

            attribution:
                "&copy; OpenStreetMap contributors"
        }
    ).addTo(map);


    const marker =
        L.marker([
            latitude,
            longitude
        ]).addTo(map);


    marker.bindPopup(
        "<strong>Special Pickup Location</strong>"
    ).openPopup();


    setTimeout(function () {

        map.invalidateSize();

    }, 300);

});

</script>

<?php endif; ?>


</body>

</html>